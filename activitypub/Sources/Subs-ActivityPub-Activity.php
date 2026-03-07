<?php
/**
 * ActivityPub Federation - Activity Builders
 *
 * Functions to construct ActivityPub activities (Create, Update, Delete,
 * Like, Announce, Accept, Reject, Follow, Undo).
 *
 * @package ActivityPub
 * @version 1.0.0
 */

if (!defined('SMF'))
	die('No direct access...');

/**
 * Build a Create activity wrapping an object.
 *
 * @param array $actor The actor record.
 * @param array $object The AP object.
 * @return array The Create activity.
 */
function activitypub_build_create_activity($actor, $object)
{
	return array(
		'@context' => 'https://www.w3.org/ns/activitystreams',
		'id' => activitypub_generate_activity_id(),
		'type' => 'Create',
		'actor' => $actor['ap_id'],
		'published' => isset($object['published']) ? $object['published'] : gmdate('Y-m-d\TH:i:s\Z'),
		'to' => array('https://www.w3.org/ns/activitystreams#Public'),
		'cc' => array($actor['followers_url']),
		'object' => $object,
	);
}

/**
 * Build an Update activity.
 *
 * @param array $actor The actor record.
 * @param array $object The updated AP object.
 * @return array The Update activity.
 */
function activitypub_build_update_activity($actor, $object)
{
	return array(
		'@context' => 'https://www.w3.org/ns/activitystreams',
		'id' => activitypub_generate_activity_id(),
		'type' => 'Update',
		'actor' => $actor['ap_id'],
		'published' => gmdate('Y-m-d\TH:i:s\Z'),
		'to' => array('https://www.w3.org/ns/activitystreams#Public'),
		'cc' => array($actor['followers_url']),
		'object' => $object,
	);
}

/**
 * Build a Delete activity.
 *
 * @param array $actor The actor record.
 * @param string $object_ap_id The AP ID of the deleted object.
 * @return array The Delete activity.
 */
function activitypub_build_delete_activity($actor, $object_ap_id)
{
	return array(
		'@context' => 'https://www.w3.org/ns/activitystreams',
		'id' => activitypub_generate_activity_id(),
		'type' => 'Delete',
		'actor' => $actor['ap_id'],
		'published' => gmdate('Y-m-d\TH:i:s\Z'),
		'to' => array('https://www.w3.org/ns/activitystreams#Public'),
		'cc' => array($actor['followers_url']),
		'object' => array(
			'id' => $object_ap_id,
			'type' => 'Tombstone',
		),
	);
}

/**
 * Build a Like activity.
 *
 * @param array $actor The actor record.
 * @param string $object_ap_id The AP ID of the liked object.
 * @return array The Like activity.
 */
function activitypub_build_like_activity($actor, $object_ap_id)
{
	return array(
		'@context' => 'https://www.w3.org/ns/activitystreams',
		'id' => activitypub_generate_activity_id(),
		'type' => 'Like',
		'actor' => $actor['ap_id'],
		'published' => gmdate('Y-m-d\TH:i:s\Z'),
		'to' => array('https://www.w3.org/ns/activitystreams#Public'),
		'cc' => array($actor['followers_url']),
		'object' => $object_ap_id,
	);
}

/**
 * Build an Announce activity (FEP-1b12 Group relay).
 * This is how boards forward content to their followers.
 *
 * @param array $actor The board/group actor record.
 * @param array $object The activity or object to announce.
 * @return array The Announce activity.
 */
function activitypub_build_announce_activity($actor, $object)
{
	$object_ref = is_array($object) && isset($object['id']) ? $object['id'] : $object;

	return array(
		'@context' => 'https://www.w3.org/ns/activitystreams',
		'id' => activitypub_generate_activity_id(),
		'type' => 'Announce',
		'actor' => $actor['ap_id'],
		'published' => gmdate('Y-m-d\TH:i:s\Z'),
		'to' => array('https://www.w3.org/ns/activitystreams#Public'),
		'cc' => array($actor['followers_url']),
		'object' => $object,
	);
}

/**
 * Build an Accept activity (in response to a Follow).
 *
 * @param array $actor The local actor accepting.
 * @param array $follow_activity The original Follow activity.
 * @return array The Accept activity.
 */
function activitypub_build_accept_activity($actor, $follow_activity)
{
	return array(
		'@context' => 'https://www.w3.org/ns/activitystreams',
		'id' => activitypub_generate_activity_id(),
		'type' => 'Accept',
		'actor' => $actor['ap_id'],
		'object' => $follow_activity,
	);
}

/**
 * Build a Reject activity (rejecting a Follow).
 *
 * @param array $actor The local actor rejecting.
 * @param array $follow_activity The original Follow activity.
 * @return array The Reject activity.
 */
function activitypub_build_reject_activity($actor, $follow_activity)
{
	return array(
		'@context' => 'https://www.w3.org/ns/activitystreams',
		'id' => activitypub_generate_activity_id(),
		'type' => 'Reject',
		'actor' => $actor['ap_id'],
		'object' => $follow_activity,
	);
}

/**
 * Build a Follow activity.
 *
 * @param array $actor The local actor following.
 * @param string $target_ap_id The AP ID of the actor to follow.
 * @return array The Follow activity.
 */
function activitypub_build_follow_activity($actor, $target_ap_id)
{
	return array(
		'@context' => 'https://www.w3.org/ns/activitystreams',
		'id' => activitypub_generate_activity_id(),
		'type' => 'Follow',
		'actor' => $actor['ap_id'],
		'object' => $target_ap_id,
	);
}

/**
 * Build an Undo activity (wraps another activity).
 *
 * @param array $actor The actor undoing.
 * @param array $activity The activity to undo.
 * @return array The Undo activity.
 */
function activitypub_build_undo_activity($actor, $activity)
{
	return array(
		'@context' => 'https://www.w3.org/ns/activitystreams',
		'id' => activitypub_generate_activity_id(),
		'type' => 'Undo',
		'actor' => $actor['ap_id'],
		'object' => $activity,
	);
}
