<?php
// haha magic Llama have been set free!

// Tamplate for LlamaKeeper 2004! or the Llama Log :p
function template_main()
{
	global $context, $modSettings, $scripturl;

	echo '
	<div class="tborder">
		<table border="0" cellspacing="1" cellpadding="4" width="100%">
			<tr class="titlebg">
				<td width="6%" align="center">&nbsp;</td>
				<td width="16%" align="center">Llama Type</td>
				<td width="7%" align="center">Points</td>
				<td width="17%" align="center">Member</td>
				<td width="25%" align="center">Time Released</td>
				<td width="25%" align="center">Time Caught</td>
			</tr>';

	if (!empty($context['llamaAdmin']))
	{
		$c=1;
		foreach ($context['llamaAdmin'] AS $llama)
		{
			echo '
			<tr>
				<td width="6%" align="center">', $c, '</td>
				<td width="16%" align="center">', $modSettings['Type'.$llama['Type']], '</td>
				<td width="7%" align="center">', (($llama['Type'] == 1) ? '+' : '-'),$llama['points'], '</td>
				<td width="17%" align="center">', (!empty($llama['member']) ? '<a href="' . $scripturl . '?action=profile;u=' . $llama['member'] . '">' . $llama['memberName'] . '</a>' : '-'), '</td>
				<td width="25%" align="center">', timeformat($llama['Released']), '</td>
				<td width="25%" align="center">', (!empty($llama['Caught']) ? timeformat($llama['Caught']) : '-'), '</td>
			</tr>
';
			$c++;
		}
	}
	else
	{
		echo '
				<td width="16%" align="center" colspan="6">No Llamas have been Released! ;(</td>
			</tr>
';
	}

	echo '
		</table>
	</div>
	<br />
	<div class="tborder">
		<table border="0" cellspacing="1" cellpadding="4" width="100%">
			<tr class="titlebg">
				<td>Llama Maintenance</td>
			</tr><tr>
				<td>
					<a href="', $scripturl, '?action=Llamalog;sa=RemoveULlamas">Remove all UnCaught Llamas</a><br />
					<a href="', $scripturl, '?action=Llamalog;sa=RemoveALlamas">Remove all Llamas</a><br /></td>
			</tr>
		</table>
	</div>';
}

// Dialog box for when a user gets a Llama!
function template_Llama_speak()
{
	global $context, $txt;

	echo '
<br /><br />
<table border="0" width="80%" cellspacing="0" align="center" cellpadding="4" class="tborder">
	<tr class="titlebg">
		<td>', $context['title'], '</td>
	</tr>
	<tr class="windowbg">
		<td style="padding-top: 3ex; padding-bottom: 3ex;">
			', $context['display'], '
		</td>
	</tr>
</table>';

	// Show a back button (using javascript.)
	echo '
<div align="center" style="margin-top: 2ex;"><a href="javascript:history.go(-1)">', $txt[250], '</a></div>';
}
?>