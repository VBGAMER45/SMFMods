<?php
/*----------------------------------------------------------------------------------/
*	My Mood                                             	                        *
*	Author: SSimple Team - 4KSTORE						 							*
*	Powered by www.smfsimple.com						   							*
************************************************************************************/

function template_boardmood_tpl_above()
{
	global $context, $txt, $scripturl, $modSettings;

	if (!empty($modSettings['mymood_enabled']) && !empty($modSettings['mymood_boardindex_where']) && $modSettings['mymood_boardindex_where'] == 'mood_board_top' && !empty($context['board_moods']) && !empty($modSettings['mymood_show_on_boardindex']))
	{
		echo '
		<span class="clear upperframe"><span></span></span>
		<div class="roundframe flow_auto">
			<div class="innerframe">
				<div class="cat_bar">
					<h3 class="catbg">
						', $txt['mymood_latestmoods_title'],'
					</h3>
				</div>
				<div class="windowbg2">
					<div class="mood_ticker">
						<ul>';
						foreach ($context['board_moods'] as $board_moods)
						{
							echo '
							<li>
								<div class="mood_authorbox">
									<div class="mood_avatar">
										', $board_moods['avatar'], '
									</div>
									<strong><a href="', $scripturl, '?action=profile;u=', $board_moods['id_member'], '">' , $board_moods['real_name'], '</a></strong>
									<span class="mood_date">', $board_moods['date'], '</span><br />
									', $board_moods['mood_content'], '
									<div class="clear"></div>
								</div>
							</li>';
						}
						echo '
						</ul>
					</div>
				</div>
			</div>
		</div>
		<span class="lowerframe"><span></span></span>';
	}
}

function template_boardmood_tpl_below()
{
	global $context, $txt, $scripturl, $modSettings;

	if (!empty($modSettings['mymood_enabled']) && !empty($modSettings['mymood_boardindex_where']) && $modSettings['mymood_boardindex_where'] == 'mood_board_bottom' && !empty($context['board_moods']) && !empty($modSettings['mymood_show_on_boardindex']))
	{
		echo '
		<span class="clear upperframe"><span></span></span>
		<div class="roundframe flow_auto">
			<div class="innerframe">
				<div class="cat_bar">
					<h3 class="catbg">
						', $txt['mymood_latestmoods_title'],'
					</h3>
				</div>
				<div class="windowbg2">
					<div class="mood_ticker">
						<ul>';
						foreach ($context['board_moods'] as $board_moods)
						{
							echo '
							<li>
								<div class="mood_authorbox">
									<div class="mood_avatar">
										', $board_moods['avatar'], '
									</div>
									<strong><a href="', $scripturl, '?action=profile;u=', $board_moods['id_member'], '">' , $board_moods['real_name'], '</a></strong>
									<span class="mood_date">', $board_moods['date'], '</span><br />
									', $board_moods['mood_content'], '
									<div class="clear"></div>
								</div>
							</li>';
						}
						echo '
						</ul>
					</div>
				</div>
			</div>
		</div>
		<span class="lowerframe"><span></span></span>';
	}
}