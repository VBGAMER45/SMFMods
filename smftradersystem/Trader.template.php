<?php
/*
SMF Trader System
Version 1.6
by:vbgamer45
http://www.smfhacks.com
*/
function template_main()
{
	global $txt, $context, $scripturl, $modSettings, $ID_MEMBER;

	$neturalcount = $context['neturalcount'];
	$pcount = $context['pcount'];
	$ncount = $context['ncount'];

	if ($context['tradecount'] != 0)
		$feedpos =  round(($pcount / $context['tradecount_nonetural']) * 100, 2);
	else
		$feedpos = 0;
		
		
	if ($modSettings['trader_use_pos_neg'])
		$context['tradecount'] = ($context['pcount'] -$context['ncount']);


echo '
<div class="tborder">
<table border="0" cellpadding="4" cellspacing="1" align="center" class="bordercolor" width="100%">
	<tr class="titlebg">
		<td height="26">' . $txt['smftrader_feedbacktitle'] . ' - ' . $context['tradername']  . '</td>
	</tr>
	<tr>
		<td class="windowbg">
			<table border="0" cellspacing="0" cellpadding="2" width="100%">
				<tr>
					<td><b>', $txt['smftrader_title'], '</b></td>
					<td><b>', $txt['smftrader_contact'], '</b></td>
				</tr>
				<tr>
					<td><b>' . $txt['smftrader_profile'] . '(' . ($modSettings['trader_use_pos_neg'] ? ($context['tradecount'] > 0 ? '+' . $context['tradecount'] : $context['tradecount']) : $context['tradecount']) . ')</b></td>
					<td><a href="' . $scripturl . '?action=profile;u=' . $context['traderid'] . '">' . $txt['smftrader_viewprofile'] . '</a></td>
				</tr>
				<tr>
					<td><b>' . $txt['smftrader_positivefeedbackpercent']  .   $feedpos . '%</b></td>
					<td><a href="' . $scripturl . '?action=pm;sa=send;u=' . $context['traderid'] . '">' . $txt['smftrader_sendpm'] . '</a></td>
				</tr>
				<tr><td>' . $txt['smftrader_positivefeedback']  . $pcount . '&nbsp;<img src="' . $modSettings['smileys_url'] . '/default/smiley.gif" alt="positive" /></td></tr>
				<tr><td>' . $txt['smftrader_neutralfeedback']   .  $neturalcount . '&nbsp;<img src="' . $modSettings['smileys_url'] . '/default/undecided.gif" alt="netural" /></td></tr>
				<tr><td>' . $txt['smftrader_negativefeedback']   .  $ncount . '&nbsp;<img src="' . $modSettings['smileys_url'] . '/default/angry.gif" alt="negative" /></td></tr>
				<tr><td>' . $txt['smftrader_totalfeedback']   .  ($pcount - $ncount) . '</td></tr>';
				

				if ($context['traderid'] != $ID_MEMBER)
				echo '<tr><td colspan="2"><br /><a href="' . $scripturl .  '?action=trader;sa=submit;id=' . $context['traderid']  . '">' . $txt['smftrader_submitfeedback'] . $context['tradername']  . '</a></td>
				</tr>';
				
				echo '
			</table>
			<hr />
			<table border="0" cellspacing="0" cellpadding="2" width="100%">
				<tr>
					<td>
				<a href="', $scripturl, '?action=trader;id=' . $context['traderid']  . '">' . $txt['smftrader_allfeedback'] . '</a>&nbsp;|&nbsp;<a href="' . $scripturl .  '?action=trader;id=' . $context['traderid']  . ';view=1">' . $txt['smftrader_sellerfeedback'] . '</a>&nbsp;|&nbsp;<a href="' . $scripturl .  '?action=trader;id=' . $context['traderid']  . ';view=2">' .  $txt['smftrader_buyerfeedback'] . '</a>&nbsp;|&nbsp;<a href="' . $scripturl .  '?action=trader;id=' . $context['traderid']  . ';view=3">' . $txt['smftrader_tradefeedback'] . '</a>
					</td>
				</tr>
			</table>
			<hr />
			<table border="0" cellspacing="0" cellpadding="2" width="100%">
			<tr>
				<td class="catbg2" width="5%">',$txt['smftrader_rating'],'</td>
				<td class="catbg2" width="55%">',$txt['smftrader_comment'],'</td>
				<td class="catbg2" width="10%">',$txt['smftrader_from'],'</td>
				<td class="catbg2" width="10%">',$txt['smftrader_detail'],'</td>
				<td class="catbg2" width="10%">',$txt['smftrader_date'],'</td>
				<td class="catbg2" width="10%">',$txt['smftrader_report'],'</td>
			</tr>
			';

			// Check if allowed to delete comment
			$deletefeedback = allowedTo('smftrader_deletefeed');

			$styleclass = 'windowbg';
			
			foreach ($context['trader_feedback'] as $row)
			{
				echo '<tr class="',$styleclass,'">';

				switch($row['salevalue'])
				{

					case 0:
					echo '<td align="center"><img src="', $modSettings['smileys_url'], '/', $context['user']['smiley_set'], '/smiley.gif" alt="positive" /></td>';

					break;
					case 1:
					echo '<td align="center"><img src="', $modSettings['smileys_url'], '/', $context['user']['smiley_set'], '/undecided.gif" alt="neutral" /></td>';
					break;
					case 2:
					echo '<td align="center"><img src="', $modSettings['smileys_url'], '/', $context['user']['smiley_set'], '/angry.gif" alt="negative" /></td>';
					break;
					default:
					echo '<td align="center">', $row['salevalue'], '</td>';
					break;
				}


				if (IsClassifiedsInstalled() == false)
				{

					if ($row['topicurl'] == '')
						echo '<td>', $row['comment_short'], '</td>';
					else
						echo '<td><a href="' . $row['topicurl'] . '">' . $row['comment_short'] . '</a></td>';

				}
				else 
				{
					if ($row['topicurl'] == '' && empty($row['ID_LISTING']))
					{
						echo '<td>', $row['comment_short'], '</td>';
					}
					else 
						echo '<td><a href="' .  (empty($row['ID_LISTING']) ?  $row['topicurl']  : $scripturl . '?action=classifieds;sa=view;id=' . $row['ID_LISTING'])  . '">' . $row['comment_short'] . '</a></td>';

					
				}
					
				$mtype = ' ';
				switch($row['saletype'])
				{
					case 0:
						$mtype = $txt['smftrader_buyer'];
					break;
					case 1:
						$mtype = $txt['smftrader_seller'];
					break;
					case 2:
						$mtype = $txt['smftrader_trade'];
					break;
					default:
					$mtype = '';
					break;
				}

				if (!empty($row['realName']))
					echo '<td>'. $mtype . '&nbsp;<a href="' . $scripturl . '?action=profile;u=' . $row['FeedBackMEMBER_ID'] . '">' . $row['realName'] . '</a></td>';
				else 
					echo '<td>'. $mtype . '&nbsp;' . $txt['smftrader_guest'] . '</td>';
				
				echo '<td><a href="' . $scripturl . '?action=trader;sa=detail;feedid=' . $row['feedbackid'] . '">',$txt['smftrader_viewdetail'],'</a></td>';
				echo '<td>', timeformat($row['saledate']), '</td>';
				echo '<td><a href="' . $scripturl . '?action=trader;sa=report;feedid=' . $row['feedbackid'] .  '">',$txt['smftrader_report'],'</a>';
				if($deletefeedback)
				{
					echo '<br /><br /><a href="' . $scripturl . '?action=trader;sa=delete;feedid=' . $row['feedbackid'] .  '">',$txt['smftrader_delete'],'</a>';
				}

				echo '</td>';
				echo '</tr>';
				
				
				if ($styleclass == 'windowbg')
					$styleclass = 'windowbg2';
				else 
					$styleclass = 'windowbg';
				
			}
			

echo '	

<tr class="titlebg">
					<td align="left" colspan="6">
					',$txt['smftrader_text_pages'],
	
					$context['page_index'],' 
					</td>
				</tr>
</table>
		</td>
	</tr>
</table>
</div>';

TraderSystemCopyright();

}

function template_submit()
{
	global $txt,  $context, $scripturl, $modSettings;
echo '
<div class="tborder">
<table border="0" cellpadding="4" cellspacing="1" align="center" class="bordercolor" width="100%">
	<tr class="titlebg">
		<td height="26" align="center">' . $txt['smftrader_submittitle'] . ' - ' . $context['tradername']  . '</td>
	</tr>
	<tr>
		<td class="windowbg">
			<form action="' . $scripturl . '?action=trader;sa=submit2" method="post">
			<table border="0" cellspacing="0" cellpadding="0" width="100%">

				<tr>
					<td width="20%"><b>' . $txt['smftrader_whoareu'] . '</b></td>
					<td align="left">
						  <select size="1" name="saletype">
						  <option value="0" selected="selected">' . $txt['smftrader_buyer'] . '</option>
						  <option value="1">' . $txt['smftrader_seller'] . '</option>
						  <option value="2">' . $txt['smftrader_trade'] . '</option>
						  </select>
					</td>
				</tr>
				<tr>
					<td width="25%">' . $txt['smftrader_transaction'] . '</td>
					<td align="left">
						  <select size="1" name="salevalue">
						  <option value="0" selected="selected">' . $txt['smftrader_positive'] . '</option>
						  <option value="1">' . $txt['smftrader_neutral'] . '</option>
						  <option value="2">' . $txt['smftrader_negative'] . '</option>
						  </select>
					</td>
				</tr>
				<tr>
					<td width="25%">' . $txt['smftrader_shortcomment'] . '</td>
					<td align="left"><input type="text" max="100" name="shortcomment" size="75" />
					<br />' . $txt['smftrader_shortcommentnote'] . '
					</td>
				</tr>';

		if (IsClassifiedsInstalled() == true)
		{
			if (count($context['class_listings_trader']) != 0)
			echo '<tr>
							<td width="25%">' . $txt['smftrader_classifieds_listing'] . '</td>
							<td align="left">
							<select name="listingid">
							<option value="0"></option>
							';
							
							foreach($context['class_listings_trader'] as $row)
								echo '<option value="' . $row['ID_LISTING'] . '">' . htmlspecialchars($row['title'],ENT_QUOTES) . ' - ' . $row['realName'] . '</option>';
			
			
							echo '
							</select>
							</td>
						</tr>';
		}

		if (isset($modSettings['class_set_trader_feedback']) && $modSettings['class_set_trader_feedback'] == 1)
		{
			// Show nothing
		}
		else
		{
			echo '
				<tr>
					<td width="25%">' . $txt['smftrader_topicurl'] . '</td>
					<td align="left"><input type="text" name="topicurl"  size="75" /></td>
				</tr>';
		}

echo '
				<tr>
					<td width="25%" valign="top">' . $txt['smftrader_longcomment'] . '</td>
					<td align="left"><textarea rows="10" name="longcomment" cols="64"></textarea></td>

				</tr>
				<tr>
					<td colspan="2" align="center"><br />
						<input type="submit" value="',$txt['smftrader_submitfeedback2'],'" name="cmdsubmit" />
					</td>
				</tr>
			</table>
			<input type="hidden" name="id" value="' . $context['traderid'] . '" />
			<input type="hidden" name="sc" value="', $context['session_id'], '" />
			</form>
		</td>
	</tr>
</table>
</div>';

TraderSystemCopyright();

}

function template_report()
{
	global $txt, $context, $scripturl;

echo '
<div class="tborder">
<table border="0" cellpadding="4" cellspacing="1" align="center" class="bordercolor" width="100%">
	<tr class="titlebg">
		<td height="26" align="center">' . $txt['smftrader_reporttitle'] . '</td>
	</tr>
	<tr>
		<td class="windowbg">
			<form action="' . $scripturl . '?action=trader;sa=report2" method="post">
			<table border="0" cellspacing="0" cellpadding="0" width="100%">
				<tr>
					<td width="25%" valign="top" align="right">', $txt['smftrader_comment'],'</td>
					<td align="left"><textarea rows="10" name="comment" cols="64"></textarea></td>

				</tr>
				<tr>
					<td colspan="2" align="center"><br />
						<input type="submit" value="', $txt['smftrader_reportfeedback'],'" name="cmdsubmit" />
					</td>
				</tr>
			</table>
			<input type="hidden" name="feedid" value="' . $context['feedid'] . '" />
			<input type="hidden" name="sc" value="', $context['session_id'], '" />
			</form>
		</td>
	</tr>
</table>
</div>';

TraderSystemCopyright();

}

function template_detail()
{
	global $txt, $context, $scripturl;

echo '
<div class="tborder">
<table border="0" cellpadding="4" cellspacing="1" align="center" class="bordercolor" width="100%">
	<tr class="titlebg">
		<td height="26" align="center">',$txt['smftrader_title'],' - ',$txt['smftrader_detailedfeedback'],'</td>
	</tr>
	<tr>
		<td class="windowbg">
			<table border="0" cellspacing="0" cellpadding="0" width="100%">
				<tr>
					<td width="25%" valign="top">',$txt['smftrader_detailedcomment'],'</td>
					<td align="left">' . parse_bbc($context['trading_detail']['comment_long']) . '<br />',$txt['smftrader_commentby'],'<a href="',$scripturl, '?action=profile;u=', $context['trading_detail']['FeedBackMEMBER_ID'],'">',$context['trading_detail']['realName'],  '</a><br /></td>

				</tr>
				<tr>
					<td colspan="2" align="center"><a href="', $scripturl, '?action=trader;id=', $context['trading_detail']['ID_MEMBER'], '">',$txt['smftrader_returntoratings'],'</a></td>
				</tr>
			</table>
		</td>
	</tr>
</table>
</div>';

TraderSystemCopyright();

}

function template_delete()
{
	global $scripturl, $context, $txt;

echo '
<div class="tborder">
<form action="', $scripturl, '?action=trader;sa=delete2" method="post">
<table border="0" cellpadding="4" cellspacing="1" align="center" class="bordercolor" width="100%">
	<tr class="titlebg">
		<td height="26" align="center">',$txt['smftrader_title'],' - ',$txt['smftrader_deletefeedback'],'</td>
	</tr>
	<tr>
		<td class="windowbg">
			<table border="0" cellspacing="0" cellpadding="0" width="100%">
				<tr>
					<td width="25%" valign="top">' . $txt['smftrader_shortcomment_small'] . '</td>
					<td align="left">' . parse_bbc($context['trader_feedback']['comment_short']) . '</td>

				</tr>
				<tr>
					<td width="25%" valign="top">',$txt['smftrader_detailedcomment'],'</td>
					<td align="left">' . parse_bbc($context['trader_feedback']['comment_long']) . '<br />',$txt['smftrader_commentby'],'<a href="' . $scripturl . '?action=profile;u=' . $context['trader_feedback']['FeedBackMEMBER_ID'] . '">' . $context['trader_feedback']['realName'] .  '</a><br /></td>

				</tr>
				<tr>
					<td colspan="2" align="center"><input type="submit" value="',$txt['smftrader_deletefeedback'],'" name="cmdsubmit" /></td>
				</tr>
			</table>
			<input type="hidden" name="feedid" value="' . $context['feedid'] . '" />
			<input type="hidden" name="redirect" value="' . $context['trader_feedback']['ID_MEMBER'] . '" />

		</td>
	</tr>
</table>
</form>
</div>';

TraderSystemCopyright();
}

function template_settings()
{
	global $scripturl, $modSettings, $txt, $context, $settings;


echo '

	<table border="0" width="80%" cellspacing="0" align="center" cellpadding="4" class="tborder">
		<tr class="titlebg">
			<td>' . $txt['smftrader_title']  . ' ' . $txt['smftrader_text_settings'] . '</td>
		</tr>
		<tr class="windowbg">
			<td>
			<b>' . $txt['smftrader_text_settings'] . '</b><br />
			<form method="POST" action="' . $scripturl . '?action=trader;sa=admin2">
			',$txt['trader_feedbackperpage'],' <input type="text" name="trader_feedbackperpage" value="', $modSettings['trader_feedbackperpage'], '" size="5" /><br /> 
			<input type="checkbox" name="trader_approval" ' . ($modSettings['trader_approval'] ? ' checked="checked" ' : '') . ' />' . $txt['smftrader_trader_approval'] . ' <a href="', $scripturl, '?action=helpadmin;help=trader_approval" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt[119], '" align="top" /></a><br />
			<input type="checkbox" name="trader_use_pos_neg" ' . ($modSettings['trader_use_pos_neg'] ? ' checked="checked" ' : '') . ' />' . $txt['trader_use_pos_neg'] . ' <a href="', $scripturl, '?action=helpadmin;help=trader_use_pos_neg" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt[119], '" align="top" /></a><br />
			
				<br />
				
				<input type="submit" name="savesettings" value="',$txt['smftrader_save_settings'],'" />
			</form><br />';
			
			// Trader Approval listings
			
			echo '
			<br />
			<form method="post" action="', $scripturl, '?action=trader;sa=bulkactions">
			<table cellspacing="0" cellpadding="10" border="0" align="center" width="90%" class="tborder">
				<tr class="catbg">
				<td class="catbg2" colspan="2" width="5%">',$txt['smftrader_rating'],'</td>
				<td class="catbg2" width="45%">',$txt['smftrader_comment'],'</td>
				<td class="catbg2" width="10%">',$txt['smftrader_to'],'</td>
				<td class="catbg2" width="10%">',$txt['smftrader_from'],'</td>
				<td class="catbg2" width="10%">',$txt['smftrader_detail'],'</td>
				<td class="catbg2" width="10%">',$txt['smftrader_date'],'</td>
				<td class="catbg2" width="10%">' . $txt['smftrader_options'] . '</td>
				</tr>';

			// List all ratings waiting for approval
			foreach ($context['trader_appoval'] as $row)
			{
				echo '<tr>';

				echo '<td><input type="checkbox" name="ratings[]" value="',$row['feedbackid'],'" /></td>';
				
				switch($row['salevalue'])
				{

					case 0:
					echo '<td align="center"><img src="', $modSettings['smileys_url'], '/', $context['user']['smiley_set'], '/smiley.gif" alt="positive" /></td>';

					break;
					case 1:
					echo '<td align="center"><img src="', $modSettings['smileys_url'], '/', $context['user']['smiley_set'], '/undecided.gif" alt="neutral" /></td>';
					break;
					case 2:
					echo '<td align="center"><img src="', $modSettings['smileys_url'], '/', $context['user']['smiley_set'], '/angry.gif" alt="negative" /></td>';
					break;
					default:
					echo '<td align="center">', $row['salevalue'], '</td>';
					break;
				}




				if($row['topicurl'] == '')
					echo '<td>', parse_bbc($row['comment_short']), '</td>';
				else
					echo '<td><a href="' . $row['topicurl'] . '">' . parse_bbc($row['comment_short']) . '</a></td>';

				$mtype = ' ';
				switch($row['saletype'])
				{
					case 0:
						$mtype = $txt['smftrader_buyer'];
					break;
					case 1:
						$mtype = $txt['smftrader_seller'];
					break;
					case 2:
						$mtype = $txt['smftrader_trade'];
					break;
					default:
					$mtype = '';
					break;
				}
				
				echo '<td><a href="' . $scripturl . '?action=profile;u=' . $row['ID_MEMBER'] . '">' . $row['mainName'] . '</a></td>';
				echo '<td>'. $mtype . '&nbsp;<a href="' . $scripturl . '?action=profile;u=' . $row['FeedBackMEMBER_ID'] . '">' . $row['realName'] . '</a></td>';
				echo '<td><a href="' . $scripturl . '?action=trader;sa=detail;feedid=' . $row['feedbackid'] . '">',$txt['smftrader_viewdetail'],'</a></td>';
				echo '<td>', timeformat($row['saledate']), '</td>';
				echo '<td><a href="' . $scripturl . '?action=trader;sa=approve;id=' . $row['feedbackid'] .  '">',$txt['smftrader_approve'],'</a>';
				echo '<br /><br /><a href="' . $scripturl . '?action=trader;sa=delete;feedid=' . $row['feedbackid'] .  '">',$txt['smftrader_delete'],'</a>';
				echo '</td>';
				echo '</tr>';

			}
	

			echo '<tr class="titlebg">
					<td align="left" colspan="8">
					',$txt['smftrader_text_pages'],
	
					$context['page_index'],' 
					
					<br /><br /><b>',$txt['smftrader_text_withselected'],'</b>
			
					<select name="doaction">
					<option value="approve">',$txt['smftrader_bulk_approveratings'],'</option>
					<option value="delete">',$txt['smftrader_bulk_deleteratings'],'</option>
					</select>
					<input type="submit" value="',$txt['smftrader_text_performaction'],'" />
					</form>
			
					</td>
				</tr>
			</table>
			</form>
			<br />
			<br />
			<b>' . $txt['smftrader_text_permissions'] . '</b><br/><span class="smalltext">' . $txt['smftrader_set_permissionnotice'] . '</span>
			<br /><a href="' . $scripturl . '?action=permissions">' . $txt['smftrader_set_editpermissions']  . '</a>

			</td>
		</tr>
<tr class="windowbg"><td><b>Has SMF Trader System helped you?</b> Then support the developers:<br />
    <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
	<input type="hidden" name="cmd" value="_xclick">
	<input type="hidden" name="business" value="sales@visualbasiczone.com">
	<input type="hidden" name="item_name" value="SMF Trader System">
	<input type="hidden" name="no_shipping" value="1">
	<input type="hidden" name="no_note" value="1">
	<input type="hidden" name="currency_code" value="USD">
	<input type="hidden" name="tax" value="0">
	<input type="hidden" name="bn" value="PP-DonationsBF">
	<input type="image" src="https://www.paypal.com/en_US/i/btn/x-click-butcc-donate.gif" border="0" name="submit" alt="Make payments with PayPal - it is fast, free and secure!" />
	<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
</form>

</td>
</tr>
</table>';

		TraderSystemCopyright();
}

function TraderSystemCopyright()
{
	// Copyright link Removal order form
	// http://www.smfhacks.com/copyright_removal.php
	echo '<div align="center"><!--Link must remain or contact me to pay to remove.-->Powered by <a href="https://www.smfhacks.com" target="blank"><span class="smalltext">SMF Trader System</span></a><!--End Copyright link--></div>';

}
?>