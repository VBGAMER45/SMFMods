<?php
function template_userinfo_response()
{
	global $context, $txt, $modSettings;

	if (!empty($context['uic']) && !empty($modSettings['uic_enable']))
	{
		echo '
		<div class="uic_content">
			<div class="uic_avatar">',  $context['uic']['avatar'], '</div>
			<div class="uic-topright">
				<div class="uic_name">
					<span>', $context['uic']['name'], '</span>
				</div>';

		if (!empty($modSettings['uic_act_group_image']))
			echo '
				<div class="uic_groupstar">
					', $context['uic']['groupstar'], '
				</div>';

			echo '
				<div class="uic_group">
					', $context['uic']['group'], '
				</div>
			</div>
			<br class="clear" />';

		if (!empty($context['uic']['personal_text']) && !empty($modSettings['uic_act_personal_text']))
			echo '
			<div class="uic_personal_text">
				', $context['uic']['personal_text'], '
			</div>';

			echo '
			<div class="uic_table">
				<table>
					<tr>
						<td width="90"><strong>', $txt['gender'], ': </strong>', $context['uic']['gender'], '</td>
						<td width="240"><strong>', $txt['posts'], ': </strong>', $context['uic']['real_posts'], '</td>
					</tr>
					<tr>
						<td valign="top"><strong>', $txt['agebb'], ': </strong>', $context['uic']['age'], '</td>
						<td><strong>', $txt['location'], ': </strong>', $context['uic']['location'], '</td>
					</tr>
				</table>
			</div>';

		if (!empty($modSettings['uic_act_contact_icons']))
			echo '
			<div class="uic_footer">
				', $context['uic']['profile_image'], '
				', $context['uic']['pm_link'], '
				', $context['uic']['web'], '
				', $context['uic']['icq'], '
				', $context['uic']['aim'], '
				', $context['uic']['yim'], '
				', $context['uic']['msn'], '
				', $context['uic']['facebook'], '
				', $context['uic']['twitter'], '
				', $context['uic']['googleplus'], '
				', $context['uic']['youtube'], '
			</div>';

		echo '
		</div>';
	}
	else
		echo $txt['uic_no_user'];
}