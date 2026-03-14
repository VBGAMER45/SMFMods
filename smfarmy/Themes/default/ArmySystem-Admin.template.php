<?php
/**
 * Army System - Admin Templates
 *
 * Admin panel template functions for the Army System. These are rendered
 * inside the SMF admin panel, NOT the regular army pages, so they use
 * standard SMF admin CSS classes (cat_bar, windowbg, roundframe, etc.)
 * rather than the army_sidebar layout.
 *
 * @package ArmySystem
 * @version 1.0
 */

/**
 * Shared admin tab bar.
 *
 * Renders the top-level admin navigation tabs (Settings, Races, Items,
 * Members, Logs) from $context['army_admin_tabs']. The active tab is
 * highlighted with a CSS class.
 */
function template_army_admin_tabs()
{
	global $context;

	$tabs = $context['army_admin_tabs'] ?? array();

	if (empty($tabs))
		return;

	echo '
		<div class="buttonlist army_admin_tabs">
			<ul>';

	foreach ($tabs as $key => $tab)
	{
		$active = !empty($tab['active']) ? ' active' : '';

		echo '
				<li>
					<a class="button', $active, '" href="', $tab['url'], '">', htmlspecialchars($tab['label']), '</a>
				</li>';
	}

	echo '
			</ul>
		</div>';
}

/**
 * Admin settings page.
 *
 * Displays the full system configuration form organized by groups
 * (General, Economy, Combat, Production, Reset, Timing, Vacation).
 * Each setting renders as a checkbox, number input, or text input
 * depending on its type.
 *
 * Context variables used:
 *   $context['army_admin_tabs']      - array, admin tab definitions
 *   $context['army_setting_groups']  - array, grouped setting definitions
 *   $context['army_admin_saved']     - bool, true if settings were just saved
 *   $context['army_session_var']     - string, session variable name
 *   $context['army_session_id']      - string, session token value
 */
function template_army_admin_settings()
{
	global $context, $txt, $scripturl;

	echo '
	<div id="admincenter">';

	template_army_admin_tabs();

	// Success message
	if (!empty($context['army_admin_saved']))
	{
		echo '
		<div class="infobox">
			', $txt['army_admin_settings_saved'] ?? 'Settings have been saved successfully.', '
		</div>';
	}

	echo '
		<form action="', $scripturl, '?action=admin;area=armysystem;sa=settings" method="post" accept-charset="UTF-8">
			<input type="hidden" name="', $context['army_session_var'], '" value="', $context['army_session_id'], '">';

	// Loop through each setting group
	foreach ($context['army_setting_groups'] as $group_key => $group)
	{
		if (empty($group['settings']))
			continue;

		echo '
			<div class="cat_bar">
				<h3 class="catbg">', htmlspecialchars($group['label']), '</h3>
			</div>
			<div class="windowbg">
				<dl class="settings">';

		foreach ($group['settings'] as $setting)
		{
			echo '
					<dt>
						<label for="', $setting['field_name'], '">', htmlspecialchars($setting['label']), '</label>
					</dt>
					<dd>';

			if ($setting['type'] === 'bool')
			{
				echo '
						<input type="checkbox" name="', $setting['field_name'], '" id="', $setting['field_name'], '" value="1"', (!empty($setting['value']) && $setting['value'] !== '0' ? ' checked' : ''), '>';
			}
			elseif ($setting['type'] === 'int')
			{
				echo '
						<input type="number" name="', $setting['field_name'], '" id="', $setting['field_name'], '" value="', (int) $setting['value'], '" class="input_text" style="width: 120px;">';
			}
			else
			{
				echo '
						<input type="text" name="', $setting['field_name'], '" id="', $setting['field_name'], '" value="', htmlspecialchars($setting['value']), '" class="input_text" style="width: 300px;">';
			}

			echo '
					</dd>';
		}

		echo '
				</dl>
			</div>';
	}

	// Save button
	echo '
			<div class="righttext" style="padding: 10px 0;">
				<input type="submit" name="save_settings" value="', $txt['army_admin_save'] ?? 'Save Settings', '" class="button">
			</div>
		</form>
	</div>';
}

/**
 * Admin races management page.
 *
 * Displays all existing races in a table with inline edit forms for each
 * row, plus an add-new-race form at the bottom. Each race shows its name,
 * seven bonus fields, member count, and Edit/Delete action buttons.
 *
 * Context variables used:
 *   $context['army_admin_tabs']            - array, admin tab definitions
 *   $context['army_races']                 - array, all races keyed by race_id
 *   $context['army_race_member_counts']    - array, member counts per race_id
 *   $context['army_bonus_fields']          - array, bonus field key => label
 *   $context['army_icon_fields']           - array, icon field key => label
 *   $context['army_admin_saved']           - bool, race was added
 *   $context['army_admin_updated']         - bool, race was updated
 *   $context['army_admin_deleted']         - bool, race was deleted
 *   $context['army_session_var']           - string, session variable name
 *   $context['army_session_id']            - string, session token value
 */
function template_army_admin_races()
{
	global $context, $txt, $scripturl;

	echo '
	<div id="admincenter">';

	template_army_admin_tabs();

	// Success messages
	if (!empty($context['army_admin_saved']))
	{
		echo '
		<div class="infobox">
			', $txt['army_admin_race_added'] ?? 'Race has been added successfully.', '
		</div>';
	}

	if (!empty($context['army_admin_updated']))
	{
		echo '
		<div class="infobox">
			', $txt['army_admin_race_updated'] ?? 'Race has been updated successfully.', '
		</div>';
	}

	if (!empty($context['army_admin_deleted']))
	{
		echo '
		<div class="infobox">
			', $txt['army_admin_race_deleted'] ?? 'Race has been deleted successfully.', '
		</div>';
	}

	// Existing races header
	echo '
		<div class="cat_bar">
			<h3 class="catbg">', $txt['army_admin_existing_races'] ?? 'Existing Races', '</h3>
		</div>';

	if (!empty($context['army_races']))
	{
		// Races table header
		echo '
		<table class="table_grid" width="100%">
			<thead>
				<tr class="title_bar">
					<th>', $txt['army_col_name'] ?? 'Name', '</th>
					<th class="centercol" style="width: 50px;">', $txt['army_col_icon'] ?? 'Icon', '</th>';

		foreach ($context['army_bonus_fields'] as $field_key => $field_label)
		{
			echo '
					<th class="centercol" style="width: 70px;">', htmlspecialchars($field_label), '</th>';
		}

		echo '
					<th class="centercol" style="width: 70px;">', $txt['army_col_members'] ?? 'Members', '</th>
					<th class="centercol" style="width: 120px;">', $txt['army_col_actions'] ?? 'Actions', '</th>
				</tr>
			</thead>
			<tbody>';

		foreach ($context['army_races'] as $race_id => $race)
		{
			$member_count = $context['army_race_member_counts'][$race_id] ?? 0;

			// Display row (shown by default)
			echo '
				<tr class="windowbg" id="army_race_view_', $race_id, '">
					<td><strong>', htmlspecialchars($race['name']), '</strong></td>
					<td class="centercol">';

			if (!empty($race['default_icon']))
				echo '<img src="', $context['army_images_url'], '/', htmlspecialchars($race['default_icon']), '" alt="" style="max-height: 32px; max-width: 32px;">';

			echo '</td>';

			foreach ($context['army_bonus_fields'] as $field_key => $field_label)
			{
				$val = isset($race[$field_key]) ? (int) $race[$field_key] : 0;
				$color = '';
				if ($val > 0)
					$color = ' style="color: green;"';
				elseif ($val < 0)
					$color = ' style="color: red;"';

				echo '
					<td class="centercol"', $color, '>', ($val > 0 ? '+' : ''), $val, '%</td>';
			}

			echo '
					<td class="centercol">', $member_count, '</td>
					<td class="centercol">
						<a href="#" onclick="document.getElementById(\'army_race_view_', $race_id, '\').style.display=\'none\'; document.getElementById(\'army_race_edit_', $race_id, '\').style.display=\'table-row\'; return false;" class="button" style="padding: 2px 8px;">', $txt['army_btn_edit'] ?? 'Edit', '</a>
					</td>
				</tr>';

			// Edit row (hidden by default)
			echo '
				<tr class="windowbg" id="army_race_edit_', $race_id, '" style="display: none;">
					<td colspan="', (count($context['army_bonus_fields']) + 4), '">
						<form action="', $scripturl, '?action=admin;area=armysystem;sa=races" method="post" accept-charset="UTF-8">
							<input type="hidden" name="', $context['army_session_var'], '" value="', $context['army_session_id'], '">
							<input type="hidden" name="race_id" value="', $race_id, '">

							<div class="roundframe">
								<dl class="settings">
									<dt>
										<label>', $txt['army_col_name'] ?? 'Name', '</label>
									</dt>
									<dd>
										<input type="text" name="race_name" value="', htmlspecialchars($race['name']), '" class="input_text" style="width: 200px;" required>
									</dd>';

			foreach ($context['army_bonus_fields'] as $field_key => $field_label)
			{
				echo '
									<dt>
										<label>', htmlspecialchars($field_label), ' (%)</label>
									</dt>
									<dd>
										<input type="number" name="', $field_key, '" value="', (int) ($race[$field_key] ?? 0), '" class="input_text" style="width: 80px;">
									</dd>';
			}

			// Icon fields (collapsible section)
			echo '
									<dt>
										<strong>', $txt['army_admin_icons'] ?? 'Icons (optional)', '</strong>
									</dt>
									<dd>&nbsp;</dd>';

			foreach ($context['army_icon_fields'] as $icon_key => $icon_label)
			{
				echo '
									<dt>
										<label>', htmlspecialchars($icon_label), '</label>
									</dt>
									<dd>
										<input type="text" name="', $icon_key, '" value="', htmlspecialchars($race[$icon_key] ?? ''), '" class="input_text" style="width: 250px;">
									</dd>';
			}

			echo '
								</dl>
								<div class="righttext">
									<input type="submit" name="edit_race" value="', $txt['army_admin_save'] ?? 'Save', '" class="button">
									<a href="#" onclick="document.getElementById(\'army_race_edit_', $race_id, '\').style.display=\'none\'; document.getElementById(\'army_race_view_', $race_id, '\').style.display=\'table-row\'; return false;" class="button">', $txt['army_btn_cancel'] ?? 'Cancel', '</a>';

			// Delete button only if no members are using this race
			if ($member_count === 0)
			{
				echo '
									<input type="submit" name="delete_race" value="', $txt['army_btn_delete'] ?? 'Delete', '" class="button" onclick="return confirm(\'', ($txt['army_admin_race_delete_confirm'] ?? 'Are you sure you want to delete this race? This cannot be undone.'), '\');" style="color: red;">';
			}

			echo '
								</div>
							</div>
						</form>
					</td>
				</tr>';
		}

		echo '
			</tbody>
		</table>';
	}
	else
	{
		echo '
		<div class="windowbg">
			<p class="centertext">', $txt['army_admin_no_races'] ?? 'No races have been created yet.', '</p>
		</div>';
	}

	// Add new race form
	echo '
		<div class="cat_bar">
			<h3 class="catbg">', $txt['army_admin_add_race'] ?? 'Add New Race', '</h3>
		</div>
		<div class="windowbg">
			<form action="', $scripturl, '?action=admin;area=armysystem;sa=races" method="post" accept-charset="UTF-8">
				<input type="hidden" name="', $context['army_session_var'], '" value="', $context['army_session_id'], '">

				<dl class="settings">
					<dt>
						<label for="new_race_name">', $txt['army_col_name'] ?? 'Name', '</label>
					</dt>
					<dd>
						<input type="text" name="race_name" id="new_race_name" value="" class="input_text" style="width: 200px;" required>
					</dd>';

	foreach ($context['army_bonus_fields'] as $field_key => $field_label)
	{
		echo '
					<dt>
						<label>', htmlspecialchars($field_label), ' (%)</label>
					</dt>
					<dd>
						<input type="number" name="', $field_key, '" value="0" class="input_text" style="width: 80px;">
					</dd>';
	}

	// Icon fields for new race
	echo '
					<dt>
						<strong>', $txt['army_admin_icons'] ?? 'Icons (optional)', '</strong>
					</dt>
					<dd>&nbsp;</dd>';

	foreach ($context['army_icon_fields'] as $icon_key => $icon_label)
	{
		echo '
					<dt>
						<label>', htmlspecialchars($icon_label), '</label>
					</dt>
					<dd>
						<input type="text" name="', $icon_key, '" value="" class="input_text" style="width: 250px;">
					</dd>';
	}

	echo '
				</dl>
				<div class="righttext">
					<input type="submit" name="add_race" value="', $txt['army_admin_add'] ?? 'Add Race', '" class="button">
				</div>
			</form>
		</div>
	</div>';
}

/**
 * Admin items management page.
 *
 * Displays items filtered by type (weapons, armor, spy tools, sentry tools,
 * fort, siege) with sub-tabs for each type. Each item is shown with inline
 * edit form and delete capability. An add-new form is at the bottom.
 *
 * Context variables used:
 *   $context['army_admin_tabs']        - array, admin tab definitions
 *   $context['army_item_tabs']         - array, item type tab definitions
 *   $context['army_items']             - array, items for current type
 *   $context['army_item_type']         - string, current type code
 *   $context['army_item_type_label']   - string, current type label
 *   $context['army_admin_saved']       - bool, item was added
 *   $context['army_admin_updated']     - bool, item was updated
 *   $context['army_admin_deleted']     - bool, item was deleted
 *   $context['army_session_var']       - string, session variable name
 *   $context['army_session_id']        - string, session token value
 */
function template_army_admin_items()
{
	global $context, $txt, $scripturl;

	echo '
	<div id="admincenter">';

	template_army_admin_tabs();

	// Item type sub-tabs
	$item_tabs = $context['army_item_tabs'] ?? array();

	if (!empty($item_tabs))
	{
		echo '
		<div class="buttonlist army_item_type_tabs" style="margin-bottom: 10px;">
			<ul>';

		foreach ($item_tabs as $type_code => $tab)
		{
			$active = !empty($tab['active']) ? ' active' : '';

			echo '
				<li>
					<a class="button', $active, '" href="', $tab['url'], '">', htmlspecialchars($tab['label']), '</a>
				</li>';
		}

		echo '
			</ul>
		</div>';
	}

	// Success messages
	if (!empty($context['army_admin_saved']))
	{
		echo '
		<div class="infobox">
			', $txt['army_admin_item_added'] ?? 'Item has been added successfully.', '
		</div>';
	}

	if (!empty($context['army_admin_updated']))
	{
		echo '
		<div class="infobox">
			', $txt['army_admin_item_updated'] ?? 'Item has been updated successfully.', '
		</div>';
	}

	if (!empty($context['army_admin_deleted']))
	{
		echo '
		<div class="infobox">
			', $txt['army_admin_item_deleted'] ?? 'Item has been deleted successfully.', '
		</div>';
	}

	// Current type items
	echo '
		<div class="cat_bar">
			<h3 class="catbg">', htmlspecialchars($context['army_item_type_label']), '</h3>
		</div>';

	if (!empty($context['army_items']))
	{
		echo '
		<table class="table_grid" width="100%">
			<thead>
				<tr class="title_bar">
					<th style="width: 40px;">#</th>
					<th>', $txt['army_col_name'] ?? 'Name', '</th>
					<th class="centercol" style="width: 50px;">', $txt['army_col_icon'] ?? 'Icon', '</th>
					<th class="centercol" style="width: 100px;">', $txt['army_col_value'] ?? 'Value/Power', '</th>
					<th class="centercol" style="width: 120px;">', $txt['army_col_price'] ?? 'Price', '</th>
					<th class="centercol" style="width: 120px;">', $txt['army_col_actions'] ?? 'Actions', '</th>
				</tr>
			</thead>
			<tbody>';

		foreach ($context['army_items'] as $item)
		{
			$item_id = (int) $item['id'];

			// Display row
			echo '
				<tr class="windowbg" id="army_item_view_', $item_id, '">
					<td>', (int) $item['number'], '</td>
					<td>', htmlspecialchars($item['name']), '</td>
					<td class="centercol">';

			if (!empty($item['icon']))
				echo '<img src="', $context['army_images_url'], '/', htmlspecialchars($item['icon']), '" alt="" style="max-height: 32px; max-width: 32px;">';

			echo '</td>
					<td class="centercol">', number_format((int) $item['value']), '</td>
					<td class="centercol">', number_format((int) $item['price']), '</td>
					<td class="centercol">
						<a href="#" onclick="document.getElementById(\'army_item_view_', $item_id, '\').style.display=\'none\'; document.getElementById(\'army_item_edit_', $item_id, '\').style.display=\'table-row\'; return false;" class="button" style="padding: 2px 8px;">', $txt['army_btn_edit'] ?? 'Edit', '</a>
					</td>
				</tr>';

			// Edit row (hidden by default)
			echo '
				<tr class="windowbg" id="army_item_edit_', $item_id, '" style="display: none;">
					<td colspan="6">
						<form action="', $scripturl, '?action=admin;area=armysystem;sa=items;type=', $context['army_item_type'], '" method="post" accept-charset="UTF-8">
							<input type="hidden" name="', $context['army_session_var'], '" value="', $context['army_session_id'], '">
							<input type="hidden" name="item_id" value="', $item_id, '">

							<div class="roundframe">
								<dl class="settings">
									<dt>
										<label>', $txt['army_col_name'] ?? 'Name', '</label>
									</dt>
									<dd>
										<input type="text" name="item_name" value="', htmlspecialchars($item['name']), '" class="input_text" style="width: 200px;" required>
									</dd>
									<dt>
										<label>', $txt['army_col_value'] ?? 'Value/Power', '</label>
									</dt>
									<dd>
										<input type="number" name="item_value" value="', (int) $item['value'], '" class="input_text" style="width: 120px;">
									</dd>
									<dt>
										<label>', $txt['army_col_price'] ?? 'Price', '</label>
									</dt>
									<dd>
										<input type="number" name="item_price" value="', (int) $item['price'], '" class="input_text" style="width: 120px;">
									</dd>
									<dt>
										<label>', $txt['army_col_letter'] ?? 'Letter Code', '</label>
									</dt>
									<dd>
										<input type="text" name="item_letter" value="', htmlspecialchars($item['letter'] ?? ''), '" class="input_text" style="width: 80px;">
									</dd>
									<dt>
										<label>', $txt['army_col_icon'] ?? 'Icon', '</label>
									</dt>
									<dd>
										<input type="text" name="item_icon" value="', htmlspecialchars($item['icon'] ?? ''), '" class="input_text" style="width: 250px;">
									</dd>
									<dt>
										<label>', $txt['army_col_repair'] ?? 'Repair Cost', '</label>
									</dt>
									<dd>
										<input type="number" name="item_repair" value="', (int) ($item['repair'] ?? 0), '" class="input_text" style="width: 120px;">
									</dd>
								</dl>
								<div class="righttext">
									<input type="submit" name="edit_item" value="', $txt['army_admin_save'] ?? 'Save', '" class="button">
									<a href="#" onclick="document.getElementById(\'army_item_edit_', $item_id, '\').style.display=\'none\'; document.getElementById(\'army_item_view_', $item_id, '\').style.display=\'table-row\'; return false;" class="button">', $txt['army_btn_cancel'] ?? 'Cancel', '</a>
									<input type="submit" name="delete_item" value="', $txt['army_btn_delete'] ?? 'Delete', '" class="button" onclick="return confirm(\'', ($txt['army_admin_item_delete_confirm'] ?? 'Are you sure you want to delete this item? This will also remove it from all player inventories.'), '\');" style="color: red;">
								</div>
							</div>
						</form>
					</td>
				</tr>';
		}

		echo '
			</tbody>
		</table>';
	}
	else
	{
		echo '
		<div class="windowbg">
			<p class="centertext">', $txt['army_admin_no_items'] ?? 'No items found for this category.', '</p>
		</div>';
	}

	// Add new item form
	echo '
		<div class="cat_bar">
			<h3 class="catbg">', $txt['army_admin_add_item'] ?? 'Add New Item', '</h3>
		</div>
		<div class="windowbg">
			<form action="', $scripturl, '?action=admin;area=armysystem;sa=items;type=', $context['army_item_type'], '" method="post" accept-charset="UTF-8">
				<input type="hidden" name="', $context['army_session_var'], '" value="', $context['army_session_id'], '">
				<input type="hidden" name="item_type" value="', $context['army_item_type'], '">

				<dl class="settings">
					<dt>
						<label for="new_item_name">', $txt['army_col_name'] ?? 'Name', '</label>
					</dt>
					<dd>
						<input type="text" name="item_name" id="new_item_name" value="" class="input_text" style="width: 200px;" required>
					</dd>
					<dt>
						<label for="new_item_value">', $txt['army_col_value'] ?? 'Value/Power', '</label>
					</dt>
					<dd>
						<input type="number" name="item_value" id="new_item_value" value="0" class="input_text" style="width: 120px;">
					</dd>
					<dt>
						<label for="new_item_price">', $txt['army_col_price'] ?? 'Price', '</label>
					</dt>
					<dd>
						<input type="number" name="item_price" id="new_item_price" value="0" class="input_text" style="width: 120px;">
					</dd>
					<dt>
						<label for="new_item_letter">', $txt['army_col_letter'] ?? 'Letter Code', '</label>
					</dt>
					<dd>
						<input type="text" name="item_letter" id="new_item_letter" value="" class="input_text" style="width: 80px;">
					</dd>
					<dt>
						<label for="new_item_icon">', $txt['army_col_icon'] ?? 'Icon', '</label>
					</dt>
					<dd>
						<input type="text" name="item_icon" id="new_item_icon" value="" class="input_text" style="width: 250px;">
					</dd>
					<dt>
						<label for="new_item_repair">', $txt['army_col_repair'] ?? 'Repair Cost', '</label>
					</dt>
					<dd>
						<input type="number" name="item_repair" id="new_item_repair" value="0" class="input_text" style="width: 120px;">
					</dd>
				</dl>
				<div class="righttext">
					<input type="submit" name="add_item" value="', $txt['army_admin_add'] ?? 'Add Item', '" class="button">
				</div>
			</form>
		</div>
	</div>';
}

/**
 * Admin members management page.
 *
 * Provides a search form to find members by name, displays search results,
 * and shows a full edit form when a specific member is loaded. Supports
 * editing all army fields, resetting to defaults, and deleting army data.
 *
 * Context variables used:
 *   $context['army_admin_tabs']            - array, admin tab definitions
 *   $context['army_admin_member']          - array|null, loaded member data
 *   $context['army_admin_search_results']  - array, search result rows
 *   $context['army_admin_search_query']    - string, last search term
 *   $context['army_races']                 - array, all races for dropdown
 *   $context['army_admin_updated']         - bool, member was updated
 *   $context['army_admin_reset']           - bool, member was reset
 *   $context['army_admin_deleted']         - bool, member was deleted
 *   $context['army_session_var']           - string, session variable name
 *   $context['army_session_id']            - string, session token value
 */
function template_army_admin_members()
{
	global $context, $txt, $scripturl;

	echo '
	<div id="admincenter">';

	template_army_admin_tabs();

	// Success / action messages
	if (!empty($context['army_admin_updated']))
	{
		echo '
		<div class="infobox">
			', $txt['army_admin_member_updated'] ?? 'Member data has been updated successfully.', '
		</div>';
	}

	if (!empty($context['army_admin_reset']))
	{
		echo '
		<div class="infobox">
			', $txt['army_admin_member_reset'] ?? 'Member has been reset to defaults.', '
		</div>';
	}

	if (!empty($context['army_admin_deleted']))
	{
		echo '
		<div class="infobox">
			', $txt['army_admin_member_deleted'] ?? 'Member army data has been deleted.', '
		</div>';
	}

	// Search form
	echo '
		<div class="cat_bar">
			<h3 class="catbg">', $txt['army_admin_search_member'] ?? 'Search Member', '</h3>
		</div>
		<div class="windowbg">
			<form action="', $scripturl, '?action=admin;area=armysystem;sa=members" method="get" accept-charset="UTF-8">
				<input type="hidden" name="action" value="admin">
				<input type="hidden" name="area" value="armysystem">
				<input type="hidden" name="sa" value="members">
				<dl class="settings">
					<dt>
						<label for="army_search">', $txt['army_admin_member_name'] ?? 'Member Name', '</label>
					</dt>
					<dd>
						<input type="text" name="search" id="army_search" value="', htmlspecialchars($context['army_admin_search_query']), '" class="input_text" style="width: 250px;">
						<input type="submit" value="', $txt['army_admin_search'] ?? 'Search', '" class="button">
					</dd>
				</dl>
			</form>
		</div>';

	// Search results
	if (!empty($context['army_admin_search_results']))
	{
		echo '
		<div class="cat_bar">
			<h3 class="catbg">', $txt['army_admin_search_results'] ?? 'Search Results', '</h3>
		</div>
		<table class="table_grid" width="100%">
			<thead>
				<tr class="title_bar">
					<th>', $txt['army_col_player'] ?? 'Player', '</th>
					<th class="centercol">', $txt['army_col_race'] ?? 'Race', '</th>
					<th class="centercol">', $txt['army_col_army_size'] ?? 'Army Size', '</th>
					<th class="centercol">', $txt['army_admin_col_status'] ?? 'Status', '</th>
					<th class="centercol" style="width: 100px;">', $txt['army_col_actions'] ?? 'Actions', '</th>
				</tr>
			</thead>
			<tbody>';

		foreach ($context['army_admin_search_results'] as $result)
		{
			$status_text = !empty($result['is_active'])
				? ($txt['army_admin_status_active'] ?? 'Active')
				: ($txt['army_admin_status_inactive'] ?? 'Inactive');

			echo '
				<tr class="windowbg">
					<td>', htmlspecialchars($result['real_name']), '</td>
					<td class="centercol">', htmlspecialchars($result['race_name']), '</td>
					<td class="centercol">', $result['army_size'], '</td>
					<td class="centercol">', $status_text, '</td>
					<td class="centercol">
						<a href="', $result['edit_url'], '" class="button" style="padding: 2px 8px;">', $txt['army_btn_edit'] ?? 'Edit', '</a>
					</td>
				</tr>';
		}

		echo '
			</tbody>
		</table>';
	}
	elseif (!empty($context['army_admin_search_query']) && $context['army_admin_member'] === null)
	{
		echo '
		<div class="windowbg">
			<p class="centertext">', $txt['army_admin_no_results'] ?? 'No members found matching your search.', '</p>
		</div>';
	}

	// Member edit form (when a specific member is loaded)
	if ($context['army_admin_member'] !== null)
	{
		$member = $context['army_admin_member'];

		echo '
		<div class="cat_bar">
			<h3 class="catbg">', $txt['army_admin_edit_member'] ?? 'Edit Member', ': ', htmlspecialchars($member['real_name']), ' (ID: ', $member['id_member'], ')</h3>
		</div>
		<div class="windowbg">
			<form action="', $scripturl, '?action=admin;area=armysystem;sa=members" method="post" accept-charset="UTF-8">
				<input type="hidden" name="', $context['army_session_var'], '" value="', $context['army_session_id'], '">
				<input type="hidden" name="member_id" value="', $member['id_member'], '">';

		// Power ratings (read-only info box)
		echo '
				<div class="roundframe">
					<h4>', $txt['army_admin_power_ratings'] ?? 'Power Ratings (Calculated)', '</h4>
					<dl class="settings">
						<dt>', $txt['army_attack_power'] ?? 'Attack Power', '</dt>
						<dd><strong>', $member['attack_power'], '</strong></dd>
						<dt>', $txt['army_defense_power'] ?? 'Defense Power', '</dt>
						<dd><strong>', $member['defense_power'], '</strong></dd>
						<dt>', $txt['army_spy_power'] ?? 'Spy Power', '</dt>
						<dd><strong>', $member['spy_power'], '</strong></dd>
						<dt>', $txt['army_sentry_power'] ?? 'Sentry Power', '</dt>
						<dd><strong>', $member['sentry_power'], '</strong></dd>
						<dt>', $txt['army_rank'] ?? 'Rank', '</dt>
						<dd>', $member['rank_level'], '</dd>
						<dt>', $txt['army_last_active'] ?? 'Last Active', '</dt>
						<dd>', $member['last_active_formatted'], '</dd>
						<dt>', $txt['army_total_attacks'] ?? 'Total Attacks', '</dt>
						<dd>', $member['total_attacks'], '</dd>
						<dt>', $txt['army_total_defends'] ?? 'Total Defends', '</dt>
						<dd>', $member['total_defends'], '</dd>
						<dt>', $txt['army_admin_inventory_count'] ?? 'Inventory Items', '</dt>
						<dd>', $member['inventory_count'], '</dd>
					</dl>
				</div>';

		// Editable fields - General
		echo '
				<div class="cat_bar">
					<h3 class="catbg">', $txt['army_admin_general'] ?? 'General', '</h3>
				</div>
				<dl class="settings">
					<dt>
						<label for="admin_race_id">', $txt['army_race'] ?? 'Race', '</label>
					</dt>
					<dd>
						<select name="race_id" id="admin_race_id">
							<option value="0"', ($member['race_id'] == 0 ? ' selected' : ''), '>', $txt['army_no_race'] ?? '-- None --', '</option>';

		foreach ($context['army_races'] as $race_id => $race)
		{
			echo '
							<option value="', $race_id, '"', ($member['race_id'] == $race_id ? ' selected' : ''), '>', htmlspecialchars($race['name']), '</option>';
		}

		echo '
						</select>
					</dd>
					<dt>
						<label for="admin_army_points">', $txt['army_gold'] ?? 'Gold', '</label>
					</dt>
					<dd>
						<input type="number" name="army_points" id="admin_army_points" value="', $member['army_points'], '" class="input_text" style="width: 150px;">
					</dd>
					<dt>
						<label for="admin_army_size">', $txt['army_army_size'] ?? 'Army Size', '</label>
					</dt>
					<dd>
						<input type="number" name="army_size" id="admin_army_size" value="', $member['army_size'], '" class="input_text" style="width: 120px;">
					</dd>
				</dl>';

		// Editable fields - Soldiers
		echo '
				<div class="cat_bar">
					<h3 class="catbg">', $txt['army_soldiers'] ?? 'Soldiers', '</h3>
				</div>
				<dl class="settings">
					<dt>
						<label for="admin_soldiers_attack">', $txt['army_soldiers_attack'] ?? 'Attack Soldiers', '</label>
					</dt>
					<dd>
						<input type="number" name="soldiers_attack" id="admin_soldiers_attack" value="', $member['soldiers_attack'], '" class="input_text" style="width: 120px;">
					</dd>
					<dt>
						<label for="admin_soldiers_defense">', $txt['army_soldiers_defense'] ?? 'Defense Soldiers', '</label>
					</dt>
					<dd>
						<input type="number" name="soldiers_defense" id="admin_soldiers_defense" value="', $member['soldiers_defense'], '" class="input_text" style="width: 120px;">
					</dd>
					<dt>
						<label for="admin_soldiers_spy">', $txt['army_soldiers_spy'] ?? 'Spy Soldiers', '</label>
					</dt>
					<dd>
						<input type="number" name="soldiers_spy" id="admin_soldiers_spy" value="', $member['soldiers_spy'], '" class="input_text" style="width: 120px;">
					</dd>
					<dt>
						<label for="admin_soldiers_sentry">', $txt['army_soldiers_sentry'] ?? 'Sentry Soldiers', '</label>
					</dt>
					<dd>
						<input type="number" name="soldiers_sentry" id="admin_soldiers_sentry" value="', $member['soldiers_sentry'], '" class="input_text" style="width: 120px;">
					</dd>
					<dt>
						<label for="admin_soldiers_untrained">', $txt['army_soldiers_untrained'] ?? 'Untrained Soldiers', '</label>
					</dt>
					<dd>
						<input type="number" name="soldiers_untrained" id="admin_soldiers_untrained" value="', $member['soldiers_untrained'], '" class="input_text" style="width: 120px;">
					</dd>
				</dl>';

		// Editable fields - Mercenaries
		echo '
				<div class="cat_bar">
					<h3 class="catbg">', $txt['army_mercenaries'] ?? 'Mercenaries', '</h3>
				</div>
				<dl class="settings">
					<dt>
						<label for="admin_mercs_attack">', $txt['army_mercs_attack'] ?? 'Attack Mercs', '</label>
					</dt>
					<dd>
						<input type="number" name="mercs_attack" id="admin_mercs_attack" value="', $member['mercs_attack'], '" class="input_text" style="width: 120px;">
					</dd>
					<dt>
						<label for="admin_mercs_defense">', $txt['army_mercs_defense'] ?? 'Defense Mercs', '</label>
					</dt>
					<dd>
						<input type="number" name="mercs_defense" id="admin_mercs_defense" value="', $member['mercs_defense'], '" class="input_text" style="width: 120px;">
					</dd>
					<dt>
						<label for="admin_mercs_untrained">', $txt['army_mercs_untrained'] ?? 'Untrained Mercs', '</label>
					</dt>
					<dd>
						<input type="number" name="mercs_untrained" id="admin_mercs_untrained" value="', $member['mercs_untrained'], '" class="input_text" style="width: 120px;">
					</dd>
				</dl>';

		// Editable fields - Upgrades & Levels
		echo '
				<div class="cat_bar">
					<h3 class="catbg">', $txt['army_admin_upgrades'] ?? 'Upgrades & Levels', '</h3>
				</div>
				<dl class="settings">
					<dt>
						<label for="admin_fort_level">', $txt['army_fort_level'] ?? 'Fort Level', '</label>
					</dt>
					<dd>
						<input type="number" name="fort_level" id="admin_fort_level" value="', $member['fort_level'], '" class="input_text" style="width: 80px;" min="1">
					</dd>
					<dt>
						<label for="admin_siege_level">', $txt['army_siege_level'] ?? 'Siege Level', '</label>
					</dt>
					<dd>
						<input type="number" name="siege_level" id="admin_siege_level" value="', $member['siege_level'], '" class="input_text" style="width: 80px;" min="1">
					</dd>
					<dt>
						<label for="admin_unit_prod_level">', $txt['army_unit_prod_level'] ?? 'Unit Production Level', '</label>
					</dt>
					<dd>
						<input type="number" name="unit_prod_level" id="admin_unit_prod_level" value="', $member['unit_prod_level'], '" class="input_text" style="width: 80px;" min="0">
					</dd>
					<dt>
						<label for="admin_spy_skill_level">', $txt['army_spy_skill_level'] ?? 'Spy Skill Level', '</label>
					</dt>
					<dd>
						<input type="number" name="spy_skill_level" id="admin_spy_skill_level" value="', $member['spy_skill_level'], '" class="input_text" style="width: 80px;" min="0">
					</dd>
					<dt>
						<label for="admin_attack_turns">', $txt['army_attack_turns'] ?? 'Attack Turns', '</label>
					</dt>
					<dd>
						<input type="number" name="attack_turns" id="admin_attack_turns" value="', $member['attack_turns'], '" class="input_text" style="width: 80px;" min="0">
					</dd>
					<dt>
						<label for="admin_is_active">', $txt['army_admin_is_active'] ?? 'Is Active', '</label>
					</dt>
					<dd>
						<input type="checkbox" name="is_active" id="admin_is_active" value="1"', (!empty($member['is_active']) ? ' checked' : ''), '>
					</dd>
				</dl>';

		// Action buttons
		echo '
				<hr>
				<div class="righttext" style="padding: 10px 0;">
					<input type="submit" name="edit_member" value="', $txt['army_admin_save_member'] ?? 'Save Changes', '" class="button">
					<input type="submit" name="reset_member" value="', $txt['army_admin_reset_member'] ?? 'Reset to Defaults', '" class="button" onclick="return confirm(\'', ($txt['army_admin_reset_confirm'] ?? 'Are you sure you want to reset this member? This will clear their inventory, clan membership, and reset all fields to defaults.'), '\');">
					<input type="submit" name="delete_member" value="', $txt['army_admin_delete_member'] ?? 'Delete Army Data', '" class="button" onclick="return confirm(\'', ($txt['army_admin_delete_confirm'] ?? 'Are you sure you want to permanently delete all army data for this member? This cannot be undone.'), '\');" style="color: red;">
				</div>
			</form>
		</div>';
	}

	echo '
	</div>';
}

/**
 * Admin logs page.
 *
 * Displays staff action logs or attack battle logs with pagination.
 * Includes sub-tabs for switching between log types and a button to
 * clear all logs of the current type.
 *
 * Context variables used:
 *   $context['army_admin_tabs']      - array, admin tab definitions
 *   $context['army_log_tabs']        - array, log type tab definitions
 *   $context['army_log_type']        - string, current log type (staff|attack)
 *   $context['army_logs']            - array, log entries for current page
 *   $context['army_total_logs']      - int, total log count
 *   $context['page_index']           - string, SMF pagination HTML
 *   $context['army_admin_cleared']   - bool, logs were just cleared
 *   $context['army_session_var']     - string, session variable name
 *   $context['army_session_id']      - string, session token value
 */
function template_army_admin_logs()
{
	global $context, $txt, $scripturl;

	echo '
	<div id="admincenter">';

	template_army_admin_tabs();

	// Log type sub-tabs
	$log_tabs = $context['army_log_tabs'] ?? array();

	if (!empty($log_tabs))
	{
		echo '
		<div class="buttonlist army_log_type_tabs" style="margin-bottom: 10px;">
			<ul>';

		foreach ($log_tabs as $type_code => $tab)
		{
			$active = !empty($tab['active']) ? ' active' : '';

			echo '
				<li>
					<a class="button', $active, '" href="', $tab['url'], '">', htmlspecialchars($tab['label']), '</a>
				</li>';
		}

		echo '
			</ul>
		</div>';
	}

	// Success message
	if (!empty($context['army_admin_cleared']))
	{
		echo '
		<div class="infobox">
			', $txt['army_admin_logs_cleared'] ?? 'Logs have been cleared successfully.', '
		</div>';
	}

	// Clear logs button
	echo '
		<form action="', $scripturl, '?action=admin;area=armysystem;sa=logs;type=', $context['army_log_type'], '" method="post" accept-charset="UTF-8" style="margin-bottom: 10px;">
			<input type="hidden" name="', $context['army_session_var'], '" value="', $context['army_session_id'], '">
			<div class="righttext">
				<span class="smalltext">', sprintf($txt['army_admin_total_logs'] ?? 'Total entries: %d', $context['army_total_logs']), '</span>
				<input type="submit" name="clear_logs" value="', $txt['army_admin_clear_logs'] ?? 'Clear All Logs', '" class="button" onclick="return confirm(\'', ($txt['army_admin_clear_confirm'] ?? 'Are you sure you want to clear all logs? This cannot be undone.'), '\');" style="color: red;">
			</div>
		</form>';

	// Pagination - top
	echo '
		<div class="pagesection">
			<div class="pagelinks">', $context['page_index'], '</div>
		</div>';

	// Staff logs view
	if ($context['army_log_type'] === 'staff')
	{
		echo '
		<div class="cat_bar">
			<h3 class="catbg">', $txt['army_admin_logs_staff'] ?? 'Staff Logs', '</h3>
		</div>
		<table class="table_grid" width="100%">
			<thead>
				<tr class="title_bar">
					<th style="width: 150px;">', $txt['army_col_date'] ?? 'Date', '</th>
					<th style="width: 130px;">', $txt['army_col_admin'] ?? 'Admin', '</th>
					<th style="width: 130px;">', $txt['army_col_target'] ?? 'Target', '</th>
					<th style="width: 120px;">', $txt['army_col_action'] ?? 'Action', '</th>
					<th>', $txt['army_col_note'] ?? 'Note', '</th>
				</tr>
			</thead>
			<tbody>';

		if (!empty($context['army_logs']))
		{
			foreach ($context['army_logs'] as $log)
			{
				$target_display = !empty($log['target_id']) ? htmlspecialchars($log['target_name']) : '-';

				echo '
				<tr class="windowbg">
					<td>', $log['time'], '</td>
					<td>', htmlspecialchars($log['member_name']), '</td>
					<td>', $target_display, '</td>
					<td><code>', htmlspecialchars($log['action']), '</code></td>
					<td>', htmlspecialchars($log['note']), '</td>
				</tr>';
			}
		}
		else
		{
			echo '
				<tr class="windowbg">
					<td colspan="5" class="centertext">', $txt['army_admin_no_logs'] ?? 'No log entries found.', '</td>
				</tr>';
		}

		echo '
			</tbody>
		</table>';
	}
	// Attack logs view
	else
	{
		echo '
		<div class="cat_bar">
			<h3 class="catbg">', $txt['army_admin_logs_attack'] ?? 'Attack Logs', '</h3>
		</div>
		<table class="table_grid" width="100%">
			<thead>
				<tr class="title_bar">
					<th style="width: 150px;">', $txt['army_col_date'] ?? 'Date', '</th>
					<th>', $txt['army_col_attacker'] ?? 'Attacker', '</th>
					<th>', $txt['army_col_defender'] ?? 'Defender', '</th>
					<th class="centercol">', $txt['army_col_atk_dmg'] ?? 'Atk Damage', '</th>
					<th class="centercol">', $txt['army_col_def_dmg'] ?? 'Def Damage', '</th>
					<th class="centercol">', $txt['army_col_gold_stolen'] ?? 'Gold Stolen', '</th>
					<th class="centercol">', $txt['army_col_atk_killed'] ?? 'Atk Killed', '</th>
					<th class="centercol">', $txt['army_col_def_killed'] ?? 'Def Killed', '</th>
				</tr>
			</thead>
			<tbody>';

		if (!empty($context['army_logs']))
		{
			foreach ($context['army_logs'] as $log)
			{
				echo '
				<tr class="windowbg">
					<td>', $log['time'], '</td>
					<td>', htmlspecialchars($log['attacker_name']), '</td>
					<td>', htmlspecialchars($log['defender_name']), '</td>
					<td class="centercol">', $log['atk_damage'], '</td>
					<td class="centercol">', $log['def_damage'], '</td>
					<td class="centercol">', $log['money_stolen'], '</td>
					<td class="centercol">', $log['atk_kill'], '</td>
					<td class="centercol">', $log['def_kill'], '</td>
				</tr>';
			}
		}
		else
		{
			echo '
				<tr class="windowbg">
					<td colspan="8" class="centertext">', $txt['army_admin_no_logs'] ?? 'No log entries found.', '</td>
				</tr>';
		}

		echo '
			</tbody>
		</table>';
	}

	// Pagination - bottom
	echo '
		<div class="pagesection">
			<div class="pagelinks">', $context['page_index'], '</div>
		</div>
	</div>';
}
