<?php
/**
 * ActivityPub Federation - English Language Strings
 *
 * @package ActivityPub
 * @version 1.0.0
 */

// Admin area
$txt['activitypub_admin'] = 'ActivityPub Federation';
$txt['activitypub_admin_desc'] = 'Configure ActivityPub federation settings for your forum.';
$txt['activitypub_settings'] = 'Settings';
$txt['activitypub_boards'] = 'Board Federation';
$txt['activitypub_blocks'] = 'Domain Blocks';
$txt['activitypub_status'] = 'Status Dashboard';

// Global settings
$txt['activitypub_enabled'] = 'Enable ActivityPub Federation';
$txt['activitypub_enabled_desc'] = 'Master switch to enable or disable all federation features.';
$txt['activitypub_auto_accept_follows'] = 'Auto-accept Follow requests';
$txt['activitypub_auto_accept_follows_desc'] = 'Automatically accept Follow requests from remote users. If disabled, follows require manual approval.';
$txt['activitypub_user_actors_enabled'] = 'Enable user Person actors';
$txt['activitypub_user_actors_enabled_desc'] = 'Allow individual users to have their own fediverse identity.';
$txt['activitypub_user_opt_in'] = 'Require user opt-in';
$txt['activitypub_user_opt_in_desc'] = 'Users must explicitly opt in to have a fediverse identity.';
$txt['activitypub_content_mode'] = 'Content type for first posts';
$txt['activitypub_content_mode_desc'] = 'How to represent topic-starting posts in the fediverse.';
$txt['activitypub_content_mode_note'] = 'Note (Mastodon-compatible)';
$txt['activitypub_content_mode_article'] = 'Article (Lemmy-compatible)';
$txt['activitypub_max_delivery_attempts'] = 'Maximum delivery attempts';
$txt['activitypub_max_delivery_attempts_desc'] = 'How many times to retry failed deliveries before abandoning.';
$txt['activitypub_delivery_batch_size'] = 'Delivery batch size';
$txt['activitypub_delivery_batch_size_desc'] = 'Number of deliveries to process per background task run.';
$txt['activitypub_rate_limit_inbox'] = 'Inbox rate limit (per domain/hour)';
$txt['activitypub_rate_limit_inbox_desc'] = 'Maximum number of inbox requests per remote domain per hour.';

// Board settings
$txt['activitypub_board_settings'] = 'Board Federation Settings';
$txt['activitypub_board_settings_desc'] = 'Configure which boards are federated and how.';
$txt['activitypub_board_enabled'] = 'Federation enabled';
$txt['activitypub_board_mode'] = 'Federation mode';
$txt['activitypub_board_handle'] = 'Fediverse handle';
$txt['activitypub_board_followers'] = 'Followers';
$txt['activitypub_board_public_only'] = 'Only publicly accessible boards can be federated.';
$txt['activitypub_board_private'] = 'Private (not federatable)';

// Domain blocks
$txt['activitypub_block_domain'] = 'Block Domain';
$txt['activitypub_block_domain_desc'] = 'Block or silence a remote domain.';
$txt['activitypub_block_type'] = 'Block type';
$txt['activitypub_block_type_block'] = 'Block (reject all activities)';
$txt['activitypub_block_type_silence'] = 'Silence (accept but hide from public)';
$txt['activitypub_block_reason'] = 'Reason';
$txt['activitypub_block_add'] = 'Add Domain Block';
$txt['activitypub_block_remove'] = 'Remove';
$txt['activitypub_block_none'] = 'No domain blocks configured.';

// Status dashboard
$txt['activitypub_status_title'] = 'Federation Status';
$txt['activitypub_status_enabled'] = 'Federation is enabled.';
$txt['activitypub_status_disabled'] = 'Federation is disabled.';
$txt['activitypub_status_federated_boards'] = 'Federated boards';
$txt['activitypub_status_total_followers'] = 'Total followers';
$txt['activitypub_status_pending_deliveries'] = 'Pending deliveries';
$txt['activitypub_status_failed_deliveries'] = 'Failed deliveries';
$txt['activitypub_status_recent_activities'] = 'Recent Activities';
$txt['activitypub_status_no_activities'] = 'No recent activities.';
$txt['activitypub_status_queue_stats'] = 'Delivery Queue';
$txt['activitypub_status_queued'] = 'Queued';
$txt['activitypub_status_delivered'] = 'Delivered';
$txt['activitypub_status_failed'] = 'Failed';
$txt['activitypub_status_abandoned'] = 'Abandoned';

// Activities
$txt['activitypub_activity_inbound'] = 'Inbound';
$txt['activitypub_activity_outbound'] = 'Outbound';
$txt['activitypub_activity_pending'] = 'Pending';
$txt['activitypub_activity_completed'] = 'Completed';
$txt['activitypub_activity_failed'] = 'Failed';

// Profile
$txt['activitypub_profile'] = 'ActivityPub';
$txt['activitypub_profile_desc'] = 'Manage your fediverse identity.';
$txt['activitypub_profile_enabled'] = 'Enable my fediverse identity';
$txt['activitypub_profile_handle'] = 'Your fediverse handle';
$txt['activitypub_profile_save'] = 'Save';

// Errors
$txt['activitypub_error_not_found'] = 'The requested ActivityPub resource was not found.';
$txt['activitypub_error_not_enabled'] = 'ActivityPub federation is not enabled.';
$txt['activitypub_error_board_not_federated'] = 'This board is not federated.';
$txt['activitypub_error_invalid_signature'] = 'Invalid HTTP Signature.';
$txt['activitypub_error_domain_blocked'] = 'This domain is blocked.';
$txt['activitypub_error_rate_limited'] = 'Rate limited. Try again later.';

// Misc
$txt['activitypub_federated_badge'] = 'Federated';
$txt['activitypub_remote_post'] = 'Remote post via ActivityPub';
$txt['activitypub_remote_user'] = 'Remote user';
$txt['activitypub_via_fediverse'] = 'via Fediverse';
