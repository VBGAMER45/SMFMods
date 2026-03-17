<?php
/******************************************************************************
 * MagicLlama.template.php - Magic Llama Mod 2.0 for SMF 2.1
 * Template functions for floating llama display, catch dialog, admin log,
 * and profile stats.
 ******************************************************************************/

/**
 * Template layer: above (empty - required by SMF layer system).
 */
function template_magic_llama_above()
{
}

/**
 * Template layer: below - outputs the floating llama HTML and JS animation.
 */
function template_magic_llama_below()
{
	global $context, $settings;

	if (empty($context['magic_llama']))
		return;

	$llama = $context['magic_llama'];

	echo '
	<div id="magic_llama_float" style="position: fixed; top: ', $llama['start_y'], 'px; left: ', $llama['start_x'], 'px; z-index: 9999; cursor: pointer;">
		<a href="', $llama['catch_url'], '" id="magic_llama_link">
			<img src="', $llama['image_url'], '" width="', $llama['width'], '" height="', $llama['height'], '" alt="Catch me!" id="magic_llama_img" />
		</a>
	</div>';

	addInlineJavaScript('
		(function() {
			var llama = document.getElementById("magic_llama_float");
			if (!llama) return;

			var vmin = 2, vmax = 4, vr = 1.5;
			var vx = vmin + vmax * Math.random();
			var vy = vmin + vmax * Math.random();
			var w = ' . $llama['width'] . ';
			var h = ' . $llama['height'] . ';
			var x = ' . $llama['start_x'] . ';
			var y = ' . $llama['start_y'] . ';
			var speed = ' . max(3, $llama['speed']) . ';

			function moveLlama() {
				var pageW = window.innerWidth;
				var pageH = window.innerHeight;

				x += vx;
				y += vy;

				vx += vr * (Math.random() - 0.5);
				vy += vr * (Math.random() - 0.5);

				if (vx > vmax + vmin) vx = (vmax + vmin) * 2 - vx;
				if (vx < -vmax - vmin) vx = (-vmax - vmin) * 2 - vx;
				if (vy > vmax + vmin) vy = (vmax + vmin) * 2 - vy;
				if (vy < -vmax - vmin) vy = (-vmax - vmin) * 2 - vy;

				if (x <= 0) { x = 0; vx = vmin + vmax * Math.random(); }
				if (x >= pageW - w) { x = pageW - w; vx = -vmin - vmax * Math.random(); }
				if (y <= 0) { y = 0; vy = vmin + vmax * Math.random(); }
				if (y >= pageH - h) { y = pageH - h; vy = -vmin - vmax * Math.random(); }

				llama.style.left = x + "px";
				llama.style.top = y + "px";
			}

			var intervalId = setInterval(moveLlama, speed);

			// Handle catch via AJAX.
			var link = document.getElementById("magic_llama_link");
			link.addEventListener("click", function(e) {
				e.preventDefault();
				clearInterval(intervalId);
				llama.style.display = "none";

				fetch(link.href + ";xml", {
					credentials: "same-origin"
				})
				.then(function(response) { return response.text(); })
				.then(function(html) {
					var overlay = document.createElement("div");
					overlay.className = "magic_llama_overlay";
					overlay.innerHTML = html;
					document.body.appendChild(overlay);

					overlay.addEventListener("click", function(e) {
						if (e.target === overlay || e.target.classList.contains("magic_llama_close")) {
							overlay.remove();
						}
					});
				})
				.catch(function() {
					window.location.href = link.href.replace(";xml", "");
				});
			});
		})();
	', true);
}

/**
 * Template: Catch result dialog (full page version).
 */
function template_magic_llama_catch()
{
	global $context, $txt, $scripturl;

	$result = $context['magic_llama_result'];
	$css_class = !empty($result['caught']) ? ($result['type'] == 1 ? 'magic_llama_good' : 'magic_llama_evil') : 'magic_llama_late';

	echo '
	<div class="magic_llama_catch_page">
		<div class="cat_bar">
			<h3 class="catbg">', $result['title'], '</h3>
		</div>
		<div class="windowbg ', $css_class, '">
			<p>', $result['message'], '</p>
		</div>
		<div class="centertext" style="margin-top: 1em;">
			<a href="javascript:history.go(-1)">', $txt['back'], '</a>
		</div>
	</div>';
}

/**
 * Template: Catch result for AJAX/XML response.
 */
function template_magic_llama_catch_xml()
{
	global $context, $txt;

	$result = $context['magic_llama_result'];
	$css_class = !empty($result['caught']) ? ($result['type'] == 1 ? 'magic_llama_good' : 'magic_llama_evil') : 'magic_llama_late';

	echo '
	<div class="magic_llama_dialog ', $css_class, '">
		<h3>', $result['title'], '</h3>
		<p>', $result['message'], '</p>
		<button class="magic_llama_close button">', $txt['magic_llama_close'], '</button>
	</div>';
}

/**
 * Template: Admin llama log.
 */
function template_magic_llama_log()
{
	global $context, $txt, $scripturl, $modSettings;

	echo '
	<div class="cat_bar">
		<h3 class="catbg">', $txt['magic_llama_log_title'], '</h3>
	</div>
	<div class="information">
		', sprintf($txt['magic_llama_total_freed'], $context['magic_llama_total_freed']), '
	</div>
	<table class="table_grid magic_llama_log_table">
		<thead>
			<tr class="title_bar">
				<th class="centertext">#</th>
				<th class="centertext">', $txt['magic_llama_log_type'], '</th>
				<th class="centertext">', $txt['magic_llama_log_points'], '</th>
				<th class="centertext">', $txt['magic_llama_log_member'], '</th>
				<th class="centertext">', $txt['magic_llama_log_released'], '</th>
				<th class="centertext">', $txt['magic_llama_log_caught'], '</th>
			</tr>
		</thead>
		<tbody>';

	if (!empty($context['magic_llama_log']))
	{
		$c = 1;
		foreach ($context['magic_llama_log'] as $llama)
		{
			echo '
			<tr class="windowbg">
				<td class="centertext">', $c++, '</td>
				<td class="centertext">', $llama['type_name'], '</td>
				<td class="centertext">', ($llama['type'] == 1 ? '+' : '-'), $llama['points'], '</td>
				<td class="centertext">', (!empty($llama['member_id']) ? '<a href="' . $scripturl . '?action=profile;u=' . $llama['member_id'] . '">' . $llama['member_name'] . '</a>' : '-'), '</td>
				<td class="centertext">', $llama['released'], '</td>
				<td class="centertext">', (!empty($llama['caught']) ? $llama['caught'] : '-'), '</td>
			</tr>';
		}
	}
	else
	{
		echo '
			<tr class="windowbg">
				<td colspan="6" class="centertext">', $txt['magic_llama_log_empty'], '</td>
			</tr>';
	}

	echo '
		</tbody>
	</table>

	<div class="cat_bar" style="margin-top: 1em;">
		<h3 class="catbg">', $txt['magic_llama_maintenance'], '</h3>
	</div>
	<div class="windowbg">
		<a href="', $context['magic_llama_remove_uncaught_url'], '" onclick="return confirm(\'', $txt['magic_llama_confirm_remove_uncaught'], '\');">', $txt['magic_llama_remove_uncaught'], '</a>
		<br />
		<a href="', $context['magic_llama_remove_all_url'], '" onclick="return confirm(\'', $txt['magic_llama_confirm_remove_all'], '\');">', $txt['magic_llama_remove_all'], '</a>
	</div>';
}

/**
 * Template: Profile llama stats.
 */
function template_magic_llama_profile()
{
	global $context, $txt, $modSettings, $scripturl;

	$stats = $context['magic_llama_stats'];
	$type1 = !empty($modSettings['magic_llama_type1_name']) ? $modSettings['magic_llama_type1_name'] : 'Golden Llama';
	$type2 = !empty($modSettings['magic_llama_type2_name']) ? $modSettings['magic_llama_type2_name'] : 'Evil Llama';

	echo '
	<div class="cat_bar">
		<h3 class="catbg">', $txt['magic_llama_profile_title'], '</h3>
	</div>
	<div class="windowbg">
		<dl class="settings magic_llama_profile_stats">
			<dt>', sprintf($txt['magic_llama_profile_good'], $type1), '</dt>
			<dd>+', $stats['good_points'], ' (', $stats['good_llamas'], ' ', $txt['magic_llama_profile_caught'], ')</dd>
			<dt>', sprintf($txt['magic_llama_profile_bad'], $type2), '</dt>
			<dd>-', $stats['bad_points'], ' (', $stats['bad_llamas'], ' ', $txt['magic_llama_profile_caught'], ')</dd>
			<dt>', $txt['magic_llama_profile_net'], '</dt>
			<dd>', ($stats['net_points'] >= 0 ? '+' : ''), $stats['net_points'], '</dd>
			<dt>', $txt['magic_llama_profile_total'], '</dt>
			<dd>', $stats['total_llamas'], '</dd>
		</dl>';

	// Show hide preference for own profile.
	if ($context['magic_llama_is_own'] && $context['magic_llama_allow_hide'])
	{
		echo '
		<hr />
		<form action="', $scripturl, '?action=profile;area=llamastats;u=', $context['user']['id'], '" method="post">
			<label>
				<input type="checkbox" name="hide_llama" value="1"', !empty($stats['hide_llama']) ? ' checked' : '', ' />
				', $txt['magic_llama_hide_option'], '
			</label>
			<br /><br />
			<input type="submit" name="save_llama_prefs" value="', $txt['save'], '" class="button" />
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
		</form>';
	}

	echo '
	</div>';
}
