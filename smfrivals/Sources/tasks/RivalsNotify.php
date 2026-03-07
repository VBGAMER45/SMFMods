<?php
/**
 * SMF Rivals - Background Task for Alert Notifications
 *
 * @package SMFRivals
 * @version 1.0.0
 */

if (!defined('SMF'))
	die('No direct access...');

/**
 * Background task that processes Rivals alert notifications.
 * Inserts into user_alerts and increments member alert counts.
 */
class Rivals_Notify_Background extends SMF_BackgroundTask
{
	/**
	 * Execute the notification task.
	 *
	 * Expected $this->_details keys:
	 * - alert_type: string (e.g., 'challenge_received')
	 * - sender_id: int (member who triggered the alert)
	 * - recipient_ids: array of int (member IDs to notify)
	 * - content_id: int (related content ID)
	 * - extra: array (additional data for alert formatting)
	 *
	 * @return bool True on success
	 */
	public function execute()
	{
		global $smcFunc, $sourcedir;

		$alert_type = $this->_details['alert_type'];
		$sender_id = (int) $this->_details['sender_id'];
		$recipient_ids = array_filter(array_map('intval', $this->_details['recipient_ids']));
		$content_id = (int) $this->_details['content_id'];
		$extra = isset($this->_details['extra']) ? $this->_details['extra'] : array();

		if (empty($recipient_ids))
			return true;

		// Remove sender from recipients
		$recipient_ids = array_diff($recipient_ids, array($sender_id));
		if (empty($recipient_ids))
			return true;

		// Get sender name
		$request = $smcFunc['db_query']('', '
			SELECT real_name
			FROM {db_prefix}members
			WHERE id_member = {int:member}',
			array('member' => $sender_id)
		);
		$sender = $smcFunc['db_fetch_assoc']($request);
		$smcFunc['db_free_result']($request);

		$extra['sender_name'] = !empty($sender['real_name']) ? $sender['real_name'] : '';

		// Check notification preferences
		require_once($sourcedir . '/Subs-Notify.php');
		$prefs = getNotifyPrefs($recipient_ids, array('rivals_' . $alert_type), true);

		$alert_rows = array();
		$alerted_members = array();

		foreach ($recipient_ids as $member_id)
		{
			$pref = !empty($prefs[$member_id]['rivals_' . $alert_type])
				? $prefs[$member_id]['rivals_' . $alert_type]
				: (!empty($prefs[0]['rivals_' . $alert_type]) ? $prefs[0]['rivals_' . $alert_type] : 0);

			// Check if alert is enabled (bit 1 = alert)
			if ($pref & self::RECEIVE_NOTIFY_ALERT)
			{
				$alert_rows[] = array(
					'alert_time' => time(),
					'id_member' => $member_id,
					'id_member_started' => $sender_id,
					'content_type' => 'rivals',
					'content_id' => $content_id,
					'content_action' => $alert_type,
					'is_read' => 0,
					'extra' => json_encode($extra),
				);
				$alerted_members[] = $member_id;
			}
		}

		// Insert alerts
		if (!empty($alert_rows))
		{
			$smcFunc['db_insert']('',
				'{db_prefix}user_alerts',
				array(
					'alert_time' => 'int',
					'id_member' => 'int',
					'id_member_started' => 'int',
					'content_type' => 'string',
					'content_id' => 'int',
					'content_action' => 'string',
					'is_read' => 'int',
					'extra' => 'string',
				),
				$alert_rows,
				array()
			);

			// Update alert counts
			updateMemberData($alerted_members, array('alerts' => '+'));
		}

		return true;
	}
}
?>