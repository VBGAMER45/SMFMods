<?php
/**
 * ActivityPub Federation - Delivery Background Task
 *
 * Processes the outbound delivery queue, sending activities
 * to remote inboxes with signed HTTP POSTs.
 *
 * Extends SMF's SMF_BackgroundTask for cron-based execution.
 *
 * @package ActivityPub
 * @version 1.0.0
 */

/**
 * Background task to deliver queued ActivityPub activities.
 */
class ActivityPub_Deliver_Background extends SMF_BackgroundTask
{
	/**
	 * Execute the delivery task.
	 *
	 * @return bool True on completion.
	 */
	public function execute()
	{
		global $sourcedir, $modSettings;

		// Load required source files.
		require_once($sourcedir . '/Subs-ActivityPub.php');
		require_once($sourcedir . '/Subs-ActivityPub-Delivery.php');

		// Check if AP is still enabled.
		if (empty($modSettings['activitypub_enabled']))
			return true;

		// Process the queue.
		$processed = activitypub_process_delivery_queue();

		// If there are more items to process, reschedule.
		if ($processed > 0)
		{
			activitypub_schedule_delivery_task();
		}

		return true;
	}
}
