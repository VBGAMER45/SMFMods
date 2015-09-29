<?php
/*
SMF Staff Page
Version 1.0
by:vbgamer45
http://www.smfhacks.com
*/
function template_main()
{
	global $db_prefix, $scripturl, $txt,$modSettings;
	//Does all the real work here for showing groups.

	//Filter groups
	if (!empty($modSettings['staff_filter']))
	{
		$groupfilter = explode(',', $modSettings['staff_filter']);
		$groupcount = count($groupfilter);
	}
	else
		$groupcount = 0;

	echo '<div class="tborder" >';

		$groups = '';

		$query = db_query("SELECT ID_GROUP, groupName, minPosts, onlineColor
			FROM {$db_prefix}membergroups WHERE minPosts = -1
			ORDER BY groupName", __FILE__, __LINE__);
		while ($row = mysql_fetch_assoc($query))
		{

			$groups[$row['ID_GROUP']]  = array(
				'id' => $row['ID_GROUP'],
				'name' => $row['groupName'],
				'color' => empty($row['onlineColor']) ? '' : $row['onlineColor'],
			);
		}
		mysql_free_result($query);


		foreach ($groups as $id => $data)
		{

			$good = 0;
			//Check filters
			for($i = 0;$i < $groupcount;$i++)
			{
				if($groupfilter[$i] == $data['name'])
				{
					$good = 1;
					break;
				}
			}

			//Skip to next group
			if($good == 1)
				continue;


			//Now get all the user's
		$query2 = db_query("SELECT ID_GROUP, ID_MEMBER, realName, lastLogin, dateRegistered
			FROM {$db_prefix}members WHERE ID_GROUP = " . $data['id'] . " ", __FILE__, __LINE__);

			if(db_affected_rows() != 0)
			{
				echo '<table border="0" cellspacing="0" cellpadding="2" width="100%">';
				echo '<tr>';
				echo '<td class="windowbg" width="30%"><b>' . $data['name'] . '</b></td>';
				echo '<td class="windowbg" width="30%"><b>Last Login:</b></td>';
				echo '<td class="windowbg" width="30%"><b>Date Registered:</b></td>';
				echo '</tr>';

				while ($row2 = mysql_fetch_assoc($query2))
				{
					echo '<tr>';
					echo '<td class="windowbg2"><a href="' . $scripturl . '?action=profile;u=' . $row2['ID_MEMBER'] . '"><font color="' . $data['color'] . '">' . $row2['realName'] . '</font></a></td>';
					echo '<td class="windowbg2">' . timeformat($row2['lastLogin']) . '</td>';
					echo '<td class="windowbg2">' .  timeformat($row2['dateRegistered']) . '</td>';
					echo '</tr>';
				}
				echo '</table>';
			}
		}
			echo '<br />';


			//Show local mod's
			$localmods = '';
			//Stores the boards that member is a moderateor of
			$bmods = '';

			$query3 = db_query("SELECT m.ID_GROUP, m.ID_MEMBER, m.realName, m.lastLogin, m.dateRegistered, b.name
			FROM {$db_prefix}members AS m, {$db_prefix}moderators AS o, {$db_prefix}boards AS b WHERE o.ID_MEMBER = m.ID_MEMBER AND b.ID_BOARD = o.ID_BOARD", __FILE__, __LINE__);
			if(db_affected_rows() != 0)
			{
				echo '<table border="0" cellspacing="0" cellpadding="2" width="100%">';

				echo '<tr>';
				echo '<td class="windowbg" width="25%"><b>' . $txt['smfstaff_local'] . '</b></td>';
				echo '<td class="windowbg" width="25%"><b>Last Active:</b></td>';
				echo '<td class="windowbg" width="25%"><b>Forums:</b></td>';
				echo '<td class="windowbg" width="25%"><b>Date Registered:</b></td>';
				echo '</tr>';
				while ($row3 = mysql_fetch_assoc($query3))
				{
					@$bmods[$row3['ID_MEMBER']] .= $row3['name']  . '<br />';

						$localmods[$row3['ID_MEMBER']]  = array(
					'id' => $row3['ID_MEMBER'],
					'realName' => $row3['realName'],
					'lastLogin' => $row3['lastLogin'],
					'dateRegistered' => $row3['dateRegistered'],
					'forums' =>  $bmods[$row3['ID_MEMBER']],
					);

				}

				foreach ($localmods  as $id => $data)
				{
						echo '<tr>';
						echo '<td class="windowbg2"><a href="' . $scripturl . '?action=profile;u=' . $data['id'] . '">' . $data['realName'] . '</a></td>';
						echo '<td class="windowbg2">' . timeformat($data['lastLogin']) . '</td>';
						echo '<td class="windowbg2">' . $data['forums'] . '</td>';
						echo '<td class="windowbg2">' .  timeformat($data['dateRegistered']) . '</td>';
						echo '</tr>';
				}

				echo '</table>';
			}
			mysql_free_result($query3);

	//Copryright keep it please. Helps support the mod making for SMF
	echo '<br /><div align="center"><a href="http://www.smfhacks.com" target="blank">SMF Staff Page</a></div>
	</div>';
}
?>