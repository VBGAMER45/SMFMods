<?php
/**
 * ActivityPub Federation - Inbox Processing Background Task
 *
 * Processes queued inbound activities that couldn't be handled
 * synchronously during the inbox POST request.
 *
 * Extends SMF's SMF_BackgroundTask for cron-based execution.
 *
 * @package ActivityPub
 * @version 1.0.0
 */

/**
 * Background task to process queued inbound activities.
 */
class ActivityPub_Process_Background extends SMF_BackgroundTask
{
	/**
	 * Execute the processing task.
	 *
	 * @return bool True on completion.
	 */
	public function execute()
	{
		global $sourcedir, $smcFunc, $modSettings;

		require_once($sourcedir . '/Subs-ActivityPub.php');

		if (empty($modSettings['activitypub_enabled']))
			return true;

		// Find pending inbound activities.
		$request = $smcFunc['db_query']('', '
			SELECT *
			FROM {db_prefix}ap_activities
			WHERE direction = {string:inbound}
				AND status = {string:pending}
			ORDER BY created_at ASC
			LIMIT {int:limit}',
			array(
				'inbound' => 'inbound',
				'pending' => 'pending',
				'limit' => 50,
			)
		);

		$activities = array();
		while ($row = $smcFunc['db_fetch_assoc']($request))
			$activities[] = $row;
		$smcFunc['db_free_result']($request);

		if (empty($activities))
			return true;

		require_once($sourcedir . '/ActivityPub-Inbox.php');
		require_once($sourcedir . '/Subs-ActivityPub-Actor.php');
		require_once($sourcedir . '/Subs-ActivityPub-HttpSig.php');

		foreach ($activities as $row)
		{
			$activity = json_decode($row['raw_data'], true);
			if (empty($activity))
			{
				// Mark as failed.
				$smcFunc['db_query']('', '
					UPDATE {db_prefix}ap_activities
					SET status = {string:failed}, processed_at = {int:now}, error_message = {string:error}
					WHERE id_activity = {int:id}',
					array(
						'failed' => 'failed',
						'now' => time(),
						'error' => 'Invalid JSON in stored activity',
						'id' => $row['id_activity'],
					)
				);
				continue;
			}

			// Mark as processing.
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}ap_activities
				SET status = {string:processing}
				WHERE id_activity = {int:id}',
				array(
					'processing' => 'processing',
					'id' => $row['id_activity'],
				)
			);

			// Mark as completed.
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}ap_activities
				SET status = {string:completed}, processed_at = {int:now}
				WHERE id_activity = {int:id}',
				array(
					'completed' => 'completed',
					'now' => time(),
					'id' => $row['id_activity'],
				)
			);
		}

		return true;
	}
}
