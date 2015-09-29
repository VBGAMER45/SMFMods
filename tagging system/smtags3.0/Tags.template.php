<?php
/*
Tagging System
Version 3.0+stef
http://www.smfhacks.com
  by: vbgamer45 and stefann
  license: this modification is licensed under the Creative Commons BY-NC-SA 3.0 License

Included icons are from Silk Icons 1.3 available at http://www.famfamfam.com/lab/icons/silk/
  and are licensed under the Creative Commons Attribution 2.5 License
*/

function template_main()
{
	global $txt,$context,$scripturl,$settings;

	echo '
		<div class="tborder">
			<table border="0" width="100%" align="center" cellspacing="1" cellpadding="4" class="bordercolor">
				<tr>
					<td align="center" class="catbg">',$txt['smftags_popular'], '</td>
				</tr>
				<tr>
					<td align="center" class="windowbg2">';

	if(isset($context['poptags']))
		echo $context['poptags'];

		echo '
					</td>
				</tr>
			</table>';

	// check we have the variables set
	if (isset($context['tags_newtagged'])) {
		echo '
			<br />
			<div align="center"><large><a href="', $scripturl ,'?action=tags;sa=viewall">', $txt['smftags_viewall'], '</a>', $txt['smftags_viewall2'], '</large></div>
			<br />
			<form name="pendingtags" action="', $scripturl, '?action=tags" method="post" accept-charset="', $context['character_set'], '">
			<table border="0" width="100%" align="center" cellspacing="1" cellpadding="4" class="bordercolor">
				<tr>
					<td align="center" class="catbg">',$txt['smftags_manage_tags'], '</td>
				</tr>
				<tr>
					<td align="center" class="windowbg2">
						<table border="0" width="100%" cellspacing="1" cellpadding="4" class="bordercolor">
							<tr>
								<td class="catbg3">',$txt['smftags_tag'],'</td>
								<td class="catbg3" align="center">',$txt['smftags_parents'],'</td>
								<td class="catbg3" align="center">', $txt['smftags_taggable'], '</td>
								<td class="catbg3" align="center">', $txt['smftags_select'], '</td>
							</tr>';
		foreach ($context['tags_newtags'] as $i => $newtag)
		{
			echo '
							<tr>
								<td class="windowbg2"><a href="' . $scripturl . '?action=tags;id=' . $newtag['ID_TAG'] . '">' . $newtag['tag'] . '</a></td>
								<td class="windowbg2">';
			if (!empty($newtag['parent_id'])) {
				$output = array();
				$parent = $newtag['parent_id'];
				while (isset($newtag['parent_array'][$parent])) {
					$output[] = $newtag['parent_array'][$parent]['tag'];
					$parent = $newtag['parent_array'][$parent]['parent_id']; 
				}
				echo implode(' &rArr; ',$output);
			}
			echo '</td>
								<td class="windowbg2">', (($newtag['taggable']) ? strtolower($txt['smftags_taggable']) : strtolower($txt['smftags_untaggable'])), '</td>
								<td class="windowbg"><input type="checkbox" name="a' . $newtag['ID_TAG'] . '" value="1"></td>
							</tr>';
		}
		echo '
						</table>
					</td>
				</tr>
				<tr class="catbg">
					<td align="right"> 
						<select name="todo">
							<option>-------</option>
							<option value="approve">', $txt['smftags_act_approve'], '</option>
							<option value="delete">', $txt['smftags_act_delete'], '</option>
						</select>
						<input type="submit" name="pendingtags" value="', $txt['smftags_act_go'], '">
						<input type="reset" value="', $txt['smftags_act_reset'], '">
					</td>
				</tr>
			</table>
			</form>

			<br />';

		if (allowedTo('smftags_create')) {
			echo '
		<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
		function addCreateTag() {
			var oldtr = document.getElementById(\'newCreateTag\');
			var newtr = document.createElement(\'tr\');
			var table = document.getElementById(\'newCreateTagtable\');
			var rows = table.getElementsByTagName("TR").length;
			newtr.innerHTML = oldtr.innerHTML;
			document.getElementById(\'tag1\').setAttribute(\'name\',\'tag\' + rows);
			document.getElementById(\'parent1\').setAttribute(\'name\',\'parent\' + rows);
			document.getElementById(\'taggable1\').setAttribute(\'name\',\'taggable\' + rows);
			document.getElementById(\'approved1\').setAttribute(\'name\',\'approved\' + rows);
			document.getElementById(\'tag1\').setAttribute(\'id\',\'tag\' + rows);
			document.getElementById(\'parent1\').setAttribute(\'id\',\'parent\' + rows);
			document.getElementById(\'taggable1\').setAttribute(\'id\',\'taggable\' + rows);
			document.getElementById(\'approved1\').setAttribute(\'id\',\'approved\' + rows);
			newtr.setAttribute(\'id\',\'newCreateTag\');
			oldtr.removeAttribute(\'id\');
			table.appendChild(newtr);
		}
	// ]]></script>
			<form name="create" action="', $scripturl, '?action=tags" method="post" accept-charset="', $context['character_set'], '">
			<table border="0" width="100%" align="center" cellspacing="1" cellpadding="4" class="bordercolor">
				<tr>
					<td align="center" class="catbg">',$txt['smftags_create'], '</td>
				</tr>
				<tr>
					<td align="center" class="windowbg2">
						<table border="0" width="100%" cellspacing="1" cellpadding="4" class="bordercolor" id="newCreateTagtable">
							<tr>
								<td class="catbg3">',$txt['smftags_tag'],'</td>
								<td class="catbg3" align="center">',$txt['smftags_parent'],'</td>
								<td class="catbg3" align="center">',$txt['smftags_taggable'],'</td>
								<td class="catbg3" align="center">',$txt['smftags_approved'],'</td>
							</tr>
							<tr id="newCreateTag">
								<td class="windowbg"><input type="text" name="tag1" id="tag1" size="50" max="256"></td>
								<td class="windowbg">
									<select name="parent1" id="parent1">
										<option value="0">- ', $txt['smftags_noparent'], ' -</option>
';
			if (include_once($settings['default_theme_dir'].'/Tags.tree.inc.php')) {
				tag_draw_optgroup(0,10);
			}
			echo '
									</select>
								<td class="windowbg"><input type="checkbox" name="taggable1" id="taggable1" value="1"></td>
								<td class="windowbg2"><input type="checkbox" name="approved1" id="approved1" value="1"></td>
							</tr>
						</table>
					</td>
				</tr>
				<tr class="catbg">
					<td colspan="3" align="right">
						<input type="button" value="Add more" onClick="addCreateTag()">
						<input type="submit" value="', $txt['smftags_act_create'], '" name="create">
						<input type="reset" value="', $txt['smftags_act_reset'], '">
					</td>
				</tr>
			</table>
			</form>';

		}

		if (isset($context['tags_newtagged']) && !empty($context['tags_newtagged'])) {
			echo '
			<br />
			<table border="0" width="100%" align="center" cellspacing="1" cellpadding="4" class="bordercolor">
				<tr>
					<td align="center" class="catbg">',$txt['smftags_manage_topictags'], '</td>
				</tr>
				<tr>
					<td align="center" class="windowbg2">
					<form name="tagusertopic" action="', $scripturl, '?action=tags" method="post" accept-charset="', $context['character_set'], '">
						<table border="0" width="100%" cellspacing="1" cellpadding="4" class="bordercolor">
							<tr>
								<td class="catbg3">',$txt['smftags_tag'],'</td>
								<td class="catbg3" align="center">',$txt['smftags_postedby'],'</td>
								<td class="catbg3" align="center">',$txt['smftags_suggestedby'],'</td>
								<td class="catbg3" align="center">', $txt['smftags_tagssuggested'], '</td>
								<td class="catbg3" align="center">', $txt['smftags_select'], '</td>
							</tr>';

				// draw header row for each unique topic/suggested by pair
			foreach ($context['tags_newtagged'] as $i => $newtagged)
			{
				$rowspan_suggestedby = (empty($context['tags_expand'])) ? '' : ' rowspan="'.count($newtagged['tags']).'"';
				echo '
							<tr>
								<td class="windowbg2"'.$rowspan_suggestedby.'><a href="' . $scripturl . '?topic=' . $newtagged['ID_TOPIC'] . '.0"'.$rowspan_suggestedby.'>' . $newtagged['subject'] . '</a></td>
								<td class="windowbg"'.$rowspan_suggestedby.'><a href="' . $scripturl . '?action=profile;u=' . $newtagged['posterID'] . '">' . $newtagged['posterName'] . '</a></td>
								<td class="windowbg2"'.$rowspan_suggestedby.'><a href="' . $scripturl . '?action=profile;u=' . $newtagged['ID_MEMBER'] . '">' . $newtagged['memberName'] . '</a></td>';
				$tl = array();
				// now look at each individual tag suggested for this pair
				if (!empty($context['tags_expand'])) {
					foreach ($newtagged['tags'] as $j => $t) {
						if ($j != 0)
							echo '
							<tr>';
						echo '
								<td class="windowbg">', ($t['approved'] ? '<i>' : '') . '<a href="' . $scripturl . '?action=tags;id=' . $t['ID_TAG'] . '">' . $t['tag'] . '</a>' . ($t['approved'] ? '</i>' : ''), '</td><td class="windowbg2">', ($t['approved'] ? '' : '<input type="checkbox" name="i' . $t['ID'] . '" value="1">'), '</td>
							</tr>';
					}
				}
				else {
					foreach ($newtagged['tags'] as $j => $t) {
						$tl[] = ($t['approved'] ? '<i>' : '') . '<a href="' . $scripturl . '?action=tags;id=' . $t['ID_TAG'] . '">' . $t['tag'] . '</a>' . ($t['approved'] ? '</i>' : '');
					}
					echo '
								<td class="windowbg">' . implode(' · ',$tl) . '</td>
								<td class="windowbg2"><input type="checkbox" name="' . $newtagged['ID_TOPIC'] . 'x' . $newtagged['ID_MEMBER'] . '" value="1"></td>';
				}
			}
			echo '
							</tr>
							<tr class="catbg">
								<td colspan="3" align="left">', (!empty($context['tags_index'])) ? '<b>'.$txt[139].':</b> '.$context['tags_index'] : '', '</td>
								<td colspan="2" align="right"> 
									<select name="todo">
										<option>-------</option>
										<option value="approveusertopic">', $txt['smftags_act_approve'], ' </option>',
				($context['tags_expand'] ? '' : '
										<option value="expandusertopic">' . $txt['smftags_act_expand'] . '</option>'), '
										<option value="deleteusertopic">', $txt['smftags_act_delete'], '</option>
									</select>
									<input type="submit" name="tagusertopic" value="', $txt['smftags_act_go'], '">
									<input type="reset" value="', $txt['smftags_act_reset'], '">
								</td>
							</tr>
						</table>
						</form>
					</td>
				</tr>
			</table>
			<br /> ';
		}
	}

	 echo '
			<br />
			<table border="0" width="100%" align="center" cellspacing="1" cellpadding="4" class="bordercolor">
				<tr>
					<td align="center" class="catbg">',$txt['smftags_latest'], '</td>
				</tr>
				<tr>
					<td align="center" class="windowbg2">
						<table border="0" width="100%" align="center" cellspacing="1" cellpadding="4" class="bordercolor">
							<tr>
								<td class="catbg3">',$txt['smftags_subject'],'</td>
								<td class="catbg3" width="11%">',$txt['smftags_startedby'],'</td>
								<td class="catbg3" width="4%" align="center">', $txt['smftags_replies'], '</td>
								<td class="catbg3" width="4%" align="center">', $txt['smftags_views'], '</td>
							</tr>';
	if (!isset($context['tags_topics'])) { $context['tags_topics'] = array(); }
		foreach ($context['tags_topics'] as $i => $topic) {
			echo '
							<tr>
								<td class="windowbg2"><a href="' . $scripturl . '?topic=' . $topic['ID_TOPIC'] . '.0">' . $topic['subject'] . '</a></td>
								<td class="windowbg"><a href="' . $scripturl . '?action=profile;u=' . $topic['ID_MEMBER'] . '">' . $topic['posterName'] . '</a></td>
								<td class="windowbg2">' . $topic['numReplies'] . '</td>
								<td class="windowbg2">' . $topic['numViews'] . '</td>
							</tr>';
		}
		echo '
						</table>
					</td>
				</tr>
			</table>
			<br />
		</div>';

	//The Copyright is required to remain or contact me to purchase link removal.
	echo '
		<br />
		<div align="center"><a href="http://www.smfhacks.com" target="blank">SMF Tags</a></div>';
}

function template_viewall() {
	global $txt,$context,$scripturl,$settings;
	if (isset($context['tags']['modifiedtxt'])) { echo '<div align="center">'.$context['tags']['modifiedtxt'].'</div>'; }
		echo '
		<div class="tborder">
			<table border="0" width="100%" align="center" cellspacing="1" cellpadding="4" class="bordercolor">
				<tr>
					<td align="center" class="catbg">',$txt['smftags_all'], '</td>
				</tr>
				<tr>
					<td align="center" class="windowbg2">';

		if(isset($context['poptags']))
			echo $context['poptags'];

		echo '
					</td>
				</tr>
			</table>
			<form method="POST" name="tags" action="', $scripturl, '?action=tags;sa=viewall">
			<table border="0" width="100%" align="center" cellspacing="1" cellpadding="4" class="bordercolor">
				<tr>
					<td class="catbg3">', $txt['smftags_tag'], '</td>
					<td class="catbg3">', $txt['smftags_count'], '</td>
					<td class="catbg3">', $txt['smftags_taggable'], '</td>
					<td class="catbg3">', $txt['smftags_select'], '</td>
				</tr>
';
		$context['tags']['rows'] = 99;
		if (@include_once($settings['default_theme_dir'].'/Tags.tree.inc.php'))
			tagadmin_draw_branch(0,5);

		echo '
				<tr class="catbg">
					<td colspan="4" align="right">
						<select name="todo">
							<option>------------------</option>';
	foreach (array('move', 'merge', 'taggable', 'untaggable', 'unapprove', 'delete') as $i) {
		echo '
							<option value="', $i, '">', $txt["smftags_act_$i"], '</option>';
	}
	echo '
						</select>
						<input type="submit" name="tags" value="', $txt['smftags_act_go'], '">
						<input type="reset" value="', $txt['smftags_act_reset'], '">
					</td>
				</tr>
			</table>
			</form>
			<p>
';
	foreach (array('merge', 'move') as $i) {
		echo '
				<b>', $txt['smftags_act_'.$i], '</b> ', $txt['smftags_desc_'.$i], '<br />';
	}
echo '
			</p>
		</div>';

		//The Copyright is required to remain or contact me to purchase link removal.
		echo '
		<br />
		<div align="center"><a href="http://www.smfhacks.com" target="blank">SMF Tags</a></div>';
}

// used for confirmation or second data page
function template_viewall2() {
	global $txt,$context,$scripturl,$settings;
	echo '
		<div class="tborder">
			<table border="0" width="100%" align="center" cellspacing="1" cellpadding="4" class="bordercolor">
				<tr>
					<td align="center" class="catbg">',$txt['smftags_popular'], '</td>
				</tr>
				<tr>
					<td align="center" class="windowbg2">';

		$ids = array();

		if(isset($context['poptags']))
				echo $context['poptags'];

		echo '</td>
				</tr>
			</table>
			<form method="POST" name="', strtolower($_REQUEST['todo']), '" action="', $scripturl, '?action=tags;sa=viewall">';
	if ($_REQUEST['todo'] == "merge") {
		echo '
			<div align="center">', $txt['smftags_merge2'], '</div>
			<table border="0" width="100%" align="center" cellspacing="1" cellpadding="4" class="bordercolor">
				<tr>
					<td class="catbg3">', $txt['smftags_tag'], '</td>
					<td class="catbg3">', $txt['smftags_count'], '</td>
					<td class="catbg3">', $txt['smftags_taggable'], '</td>
					<td class="catbg3">', $txt['smftags_select'], '</td>
				</tr>';
		foreach ($context['tags']['by_parent'] as $parent => $children) {
			foreach ($children as $child) {
				list($id,$tag,$approved1,$taggable,$tagged,$approved2,$quantity) = $child;
				$bg = (empty($bg)) ? 2 : "";
				echo '
				<tr>
					<td class="windowbg', $bg, '"><a href="', $scripturl, '?action=tags;id=', $id, '">', $tag, '</a></td>
					<td class="windowbg', $bg, '">', $quantity, '</td>
					<td class="windowbg', $bg, '">', $taggable, '</td>
					<td class="windowbg', $bg, '"><input type="radio" name="master" value="tag[', $id, ']"></td>
				</tr>';
				$ids[] = $id;
			}
		}
		echo '
				<tr class="catbg">
					<td colspan="4" align="right">
						<input type="submit" name="merge" value="', $txt['smftags_act_merge'], '">
						<input type="reset" value="', $txt['smftags_act_reset'], '">
					</td>
				</tr>
			</table>
			<input type="hidden" name="slaves" value="', implode(',', $ids), '">';
	}
	else if ($_REQUEST['todo'] == "move") {
		echo '
		<div align="center">', $txt['smftags_move2'], '</div>
			<table border="0" width="100%" align="center" cellspacing="1" cellpadding="4" class="bordercolor">
				<tr>
					<td class="catbg3">Tag</td>
					<td class="catbg3">Count</td>
					<td class="catbg3">Taggable</td>
					<td class="catbg3">Select</td>
				</tr>';
		if (@include_once($settings['default_theme_dir'].'/Tags.tree.inc.php'))
			// using depth = -2 to include the root element and stop recursion, as moving to a child tag would create orphans
			tagadmin_draw_branch(0, 4, -2, "", 0, "radio");

		foreach ($context['tags']['by_parent'] as $parent => $children) { foreach ($children as $child) { $ids[] = $child[0]; } }

		echo '
				<input type="hidden" name="slaves" value="', implode(',', $ids), '">
				<tr class="catbg">
					<td colspan="4" align="right">
						<input type="submit" name="move" value="', $txt['smftags_act_move'], '">
						<input type="reset" value="', $txt['smftags_act_reset'], '">
					</td>
				</tr>
			</table>
			</form>';
	}
	else {
		fatal_error($txt['smftags_err_nodirect']);
	}

	echo '
			<br />
		</div>';

		//The Copyright is required to remain or contact me to purchase link removal.
		echo '
		<br />
		<div align="center"><a href="http://www.smfhacks.com" target="blank">SMF Tags</a></div>';
}

function template_results()
{
	global $scripturl, $txt, $context, $user_info;
	if (empty($context['tag_search'])) { fatal_error($txt['smftags_err_notag']); }

	echo '
		<div class="tborder">
			<table border="0" width="100%" align="center" cellspacing="1" cellpadding="4" class="bordercolor">
				<tr>
					<td align="center" class="catbg">' . $txt['smftags_resultsfor'] . implode(', ', $context['tag_search']) . '</td>
				</tr>
				<tr>
					<td>
						<table border="0" width="100%" align="center" cellspacing="1" cellpadding="4" class="bordercolor">
							<tr>
								<td class="catbg3">',$txt['smftags_subject'],'</td>
								<td class="catbg3">',$txt['smftags_othertags'],'</td>
								<td class="catbg3">',$txt['smftags_startedby'],'</td>
								<td class="catbg3" align="center">',$txt['smftags_replies'],'</td>
								<td class="catbg3" align="center">', $txt['smftags_views'], '</td>
							</tr>';
	foreach ($context['tags_topics'] as $i => $topic) {
		echo '
							<tr>
								<td class="windowbg2"><a href="' . $scripturl . '?topic=' . $topic['ID_TOPIC'] . '.0">' . $topic['subject'] . '</a></td>';
		$tl = array();
		foreach ($topic['othertags'] as $j => $t)
			$tl[] = '<a href="' . $scripturl . '?action=tags;id=' . $_REQUEST['id'] . ',' . $j . '">' . $t . '</a>';
		echo '
								<td class="windowbg2">' . implode(' · ',$tl) . '</td>
								<td class="windowbg"><a href="' . $scripturl . '?action=profile;u=' . $topic['first_ID_MEMBER'] . '">' . $topic['first_posterName'] . '</a></td>
								<td class="windowbg2">' . $topic['numReplies'] . '</td>
								<td class="windowbg2">' . $topic['numViews'] . '</td>
							</tr>';
	}
	echo '
						</table>
					</td>
				</tr>
			</table>
		</div>';

	if (allowedTo('smftags_manage')) { 
		echo '
		<form method="POST" name="rename" action="', $scripturl, '?action=tags;sa=rename">';
		foreach ($context['tag_search'] as $id => $tag) {
			echo '
		'.$txt['smftags_renametag'].' `'.$tag.'` '.$txt['smftags_renametag_to'].': <input type="text" value="'.$tag.'" name="rename'.$id.'"><br />';
		} 
		echo '
		<input type="submit" name="rename" value="', $txt['smftags_act_rename'], '">
		<input type="reset" value="', $txt['smftags_act_reset'], '">
		</form>';
	}

	//The Copyright is required to remain or contact me to purchase link removal.
	echo '
		<br />
		<div align="center"><a href="http://www.smfhacks.com" target="blank">SMF Tags</a></div>';
}
function template_edittopic()
{
	global $scripturl, $txt, $context, $settings, $sourcedir, $modSettings;
	if ($modSettings['smftags_set_listtags']) {
		$act = $context['smftags_act'];
		require_once($settings['default_theme_dir'].'/Tags.tree.inc.php');
		$context['tags']['rows'] = ($modSettings['smftags_set_listcols'] < 1) ? 0 : ceil(count($context['tags']['by_parent'][0]) /	$modSettings['smftags_set_listcols']); 
		tag_draw_js();
		echo '
		<div class="tborder">
			<form method="POST" name="updated" action="', $scripturl, '?action=tags;sa=', $act, 'topic2">
			<input type="hidden" name="type" value="2">
			<table border="0" width="100%" align="center" cellspacing="1" cellpadding="4" class="bordercolor">
				<tr>
					<td width="50%" height="19" align="center" class="catbg"><b>', $txt["smftags_${act}topic"], '</b></td>
				</tr>
				<tr valign="top">
					<td class="windowbg2">
';
		tag_draw_branch($context['smftags_flag'],0,5,0,0);
		echo '
					</td>
				</tr>
				<tr>
					<td width="28%" height="26" align="center" class="windowbg2">', ($context['tags']['show_rmsuggest']) ? '<input type="checkbox" name="tags_rmsuggest" value="1">'.$txt['smftags_rmsuggest'].'<br />' : '', '
						<input type="hidden" name="topic" value="', $context['tags_topic'], '" />
						<input type="submit" name="update" value="', $txt['smftags_act_update'], '" />
						<input type="reset" value="', $txt['smftags_act_reset'], '" />
					</td>
				</tr>
			</table>
			</form>
		</div>';
		//The Copyright is required to remain or contact me to purchase link removal.
		echo '
		<br />
		<div align="center"><a href="http://www.smfhacks.com" target="blank">SMF Tags</a></div>';
	}
}
function template_admin_settings()
{
	global $scripturl, $txt, $modSettings;

	echo '
		<table border="0" width="80%" cellspacing="0" align="center" cellpadding="4" class="tborder">
			<tr class="titlebg">
				<td>' . $txt['smftags_settings']. '</td>
			</tr>
			<tr class="windowbg">
				<td>
					<b>' . $txt['smftags_settings']. '</b><br />
					<form method="post" action="' . $scripturl . '?action=tags;sa=admin2">
					<table border="0" width="100%" align="center" cellspacing="1" cellpadding="4">
						<tr>
							<td width="30%">' . $txt['smftags_set_taggable'] . '</td>
							<td><input type="text" name="smftags_set_taggable" value="' . $modSettings['smftags_set_taggable'] . '" /></td>
						</tr>
						<tr>
							<td width="30%">' . $txt['smftags_set_type'] . '</td>
							<td>
								<label for="smftags_set_manualtags">
								<input type="checkbox" name="smftags_set_manualtags" value="1"', (($modSettings['smftags_set_manualtags']) ? ' checked' : ''), ' /> ' . $txt['smftags_set_manualtags'] . '
								<label for="smftags_set_listtags">
								<input type="checkbox" name="smftags_set_listtags" value="1"', (($modSettings['smftags_set_listtags']) ? ' checked' : ''), ' /> ' . $txt['smftags_set_listtags'] . '
							</td>
						</tr>
						<tr>
							<td width="30%">' . $txt['smftags_set_display'] . '</td>
							<td>
								<label for="smftags_set_display_top">
								<input type="checkbox" name="smftags_set_display_top" value="1"', (($modSettings['smftags_set_display_top']) ? ' checked' : ''), ' /> ' . $txt['smftags_set_display_top'] . '
								<label for="smftags_set_display_bottom">
								<input type="checkbox" name="smftags_set_display_bottom" value="1"', (($modSettings['smftags_set_display_bottom']) ? ' checked' : ''), ' /> ' . $txt['smftags_set_display_bottom'] . '
								<label for="smftags_set_display_messageindex">
								<input type="checkbox" name="smftags_set_display_messageindex" value="1"', (($modSettings['smftags_set_display_messageindex']) ? ' checked' : ''), ' /> ' . $txt['smftags_set_display_messageindex'] . '
							</td>
						</tr>
						<tr>
							<td width="30%">' . $txt['smftags_set_delimiter'] . '</td>
							<td><input type="text" size="1" maxlength="1" name="smftags_set_delimiter" value="' . chr($modSettings['smftags_set_delimiter']) . '" /></td>
						</tr>
						<tr>
							<td width="30%">' . $txt['smftags_set_listcols'] . '</td>
							<td><input type="text" name="smftags_set_listcols" value="' .	$modSettings['smftags_set_listcols'] . '" /></td>
						</tr>
						<tr>
							<td width="30%">' . $txt['smftags_set_mintaglength'] . '</td>
							<td><input type="text" name="smftags_set_mintaglength" value="' .	$modSettings['smftags_set_mintaglength'] . '" /></td>
						</tr>
						<tr>
							<td width="30%">' . $txt['smftags_set_maxtaglength'] . '</td>
							<td><input type="text" name="smftags_set_maxtaglength" value="' .	$modSettings['smftags_set_maxtaglength'] . '" /></td>
						</tr>
						<tr>
							<td width="30%">' . $txt['smftags_set_maxtags'] . '</td>
							<td><input type="text" name="smftags_set_maxtags" value="' .	$modSettings['smftags_set_maxtags'] . '" /></td>
						</tr>
						<tr>
							<td width="30%">' . $txt['smftags_set_latest_limit'] . '</td>
							<td><input type="text" name="smftags_set_latest_limit" value="' .	$modSettings['smftags_set_latest_limit'] . '" /></td>
						</tr>
						<tr>
							<td clospan="2"><b>',$txt['smftags_tagcloud_settings'],'</b></td>
						</tr>
						<tr>
							<td width="30%">' . $txt['smftags_set_cloud_sort'] . '</td>
							<td>
								<select name="smftags_set_cloud_sort">
									<option value="alpha"', ($modSettings['smftags_set_cloud_sort'] == "alpha" ? ' selected' : ''), '>' . $txt['smftags_set_cloud_sort_alpha'] . '</option>
									<option value="count"', ($modSettings['smftags_set_cloud_sort'] == "count" ? ' selected' : ''), '>' . $txt['smftags_set_cloud_sort_count'] . '</option>
									<option value="random"', ($modSettings['smftags_set_cloud_sort'] == "random" ? ' selected' : ''), '>' . $txt['smftags_set_cloud_sort_random'] . '</option>
								</select>
							</td>
						</tr>
						<tr>
							<td width="30%">' . $txt['smftags_set_cloud_tags_to_show'] . '</td>
							<td><input type="text" name="smftags_set_cloud_tags_to_show" value="' .	$modSettings['smftags_set_cloud_tags_to_show'] . '" /></td>
						</tr>
						<tr>
							<td width="30%">' . $txt['smftags_set_cloud_max_font_size_precent'] . '</td>
							<td><input type="text" name="smftags_set_cloud_max_font_size_precent" value="' .	$modSettings['smftags_set_cloud_max_font_size_precent'] . '" /></td>
						</tr>
						<tr>
							<td width="30%">' . $txt['smftags_set_cloud_min_font_size_precent'] . '</td>
							<td><input type="text" name="smftags_set_cloud_min_font_size_precent" value="' .	$modSettings['smftags_set_cloud_min_font_size_precent'] . '" /></td>
						</tr>
					</table>
					<input type="submit" name="savesettings" value="', $txt['smftags_savesettings'],	'" />
					<input type="reset" value="', $txt['smftags_act_reset'],	'" />
					</form>
					<b>Has SMF Tags helped you?</b> Then support the developers:<br />
					<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
					<input type="hidden" name="cmd" value="_xclick">
					<input type="hidden" name="business" value="sales@visualbasiczone.com">
					<input type="hidden" name="item_name" value="SMF Tags">
					<input type="hidden" name="no_shipping" value="1">
					<input type="hidden" name="no_note" value="1">
					<input type="hidden" name="currency_code" value="USD">
					<input type="hidden" name="tax" value="0">
					<input type="hidden" name="bn" value="PP-DonationsBF">
					<input type="image" src="https://www.paypal.com/en_US/i/btn/x-click-butcc-donate.gif" border="0" name="submit" alt="Make payments with PayPal - it is fast, free and secure!">
					<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1" />
					</form>
				</td>
			</tr>
		</table>';

	//The Copyright is required to remain or contact me to purchase link removal.
	echo '
		<br />
		<div align="center"><a href="http://www.smfhacks.com" target="blank">SMF Tags</a></div>';
}

?>
