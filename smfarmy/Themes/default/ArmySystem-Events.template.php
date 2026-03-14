<?php
/**
 * Army System - Events Feed Template
 *
 * Provides the template function for the event feed page, displaying a
 * paginated, chronological list of army-wide events such as attacks,
 * level-ups, revives, transfers, and donations.
 *
 * @package ArmySystem
 * @version 1.0
 */

/**
 * Event feed template.
 *
 * Displays a paginated list of events. Each event is styled with a
 * CSS class based on its type for visual differentiation. Event text
 * is pre-processed by the controller with member names already
 * converted to HTML links.
 *
 * Event types (from original IPB system):
 *   1 = attack result
 *   2 = level up
 *   3 = revive
 *   4 = item transfer
 *   6 = money transfer / donation
 *
 * Context variables used:
 *   $context['army_events']     - array, events for current page
 *     ['id']           - int, event id
 *     ['time']         - string, formatted timestamp
 *     ['text']         - string, pre-processed event text with HTML links
 *     ['type']         - int, event type code
 *     ['type_class']   - string, CSS class name for this event type
 *   $context['page_index']      - string, SMF pagination HTML
 *   $context['total_events']    - int, total event count
 */
function template_army_events()
{
	global $context, $txt, $scripturl;

	echo '
	<div class="army_wrapper">';

	template_army_sidebar();

	echo '
		<div class="army_content">
			<div class="cat_bar">
				<h3 class="catbg">', $txt['army_events_title'] ?? 'Event Feed', '</h3>
			</div>';

	// Pagination - top
	echo '
			<div class="pagesection">
				<div class="pagelinks">', $context['page_index'], '</div>
			</div>';

	if (!empty($context['army_events']))
	{
		echo '
			<div class="army_events_list">';

		foreach ($context['army_events'] as $event)
		{
			// Determine the type-specific CSS class
			$type_class = $event['type_class'] ?? 'army_event_default';

			echo '
				<div class="windowbg army_event_item ', $type_class, '">
					<span class="army_event_time smalltext">', $event['time'], '</span>
					<span class="army_event_text">', $event['text'], '</span>
				</div>';
		}

		echo '
			</div>';
	}
	else
	{
		echo '
			<div class="windowbg">
				<p class="centertext">', $txt['army_no_events'] ?? 'No events to display.', '</p>
			</div>';
	}

	// Pagination - bottom
	echo '
			<div class="pagesection">
				<div class="pagelinks">', $context['page_index'], '</div>
			</div>
		</div>
	</div>';
}
