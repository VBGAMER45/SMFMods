<?php

function template_worlds()
{
	global $context, $scripturl;


	echo '
<div class="cat_bar">
		<h3 class="catbg centertext">
		Your Worlds
		</h3>
  </div>
	<table cellspacing="0" cellpadding="5" border="0" align="center" width="100%" class="tborder">
				<tr class="titlebg">
					<td align="center">Title</td>
					<td align="center">Server Version</td>
					<td align="center">Enabled</td>
					<td align="center">Options</td>
				</tr>';

	foreach($context['rpgwo_worlds'] as $row)
	{
		echo '<tr class="windowbg2">
				<td align="center"><a href="' . $scripturl . '?action=rpgwo;sa=editworld;id=' . $row['ID_SERVER']. '">'  . $row['title'] . '</a></td>
				<td align="center">';

				if ($row['server_version'] == 1)
					echo 'V1';

				if ($row['server_version'] == 2)
					echo 'V2';

				if ($row['server_version'] == 3)
					echo 'V3';

		echo '</td>
				<td align="center">';

					if ($row['enabled'])
						echo 'Yes';
					else
						echo 'No';


		echo '</td>

			  <td align="center"><a href="' . $scripturl . '?action=rpgwo;sa=editworld;id=' . $row['ID_SERVER'] . '">[Edit World]</a> 
			  <a href="' . $scripturl . '?action=rpgwo;sa=deleteworld;id=' . $row['ID_SERVER'] . '">[Delete World]</a> 
			  </td>
			  </tr>';
	}
	echo '
			<tr class="titlebg">
				<td align="center" colspan="4"><a href="' . $scripturl . '?action=rpgwo;sa=addworld">Add World</a></td>
			</tr>
	</table>';




}


function template_add_world()
{
	global $context, $scripturl;

	echo '
<form method="post" action="' . $scripturl . '?action=rpgwo;sa=addworld2">
<div class="cat_bar">
		<h3 class="catbg centertext">
		Add World
		</h3>
  </div>';

  if (!empty($context['rpgwo_errors']))
  {
	echo '<div class="errorbox" id="errors">
						<dl>
							<dt>
								<strong style="" id="error_serious">Errors</strong>
							</dt>
							<dt class="error" id="error_list">';

							foreach($context['rpgwo_errors'] as $msg)
								echo $msg . '<br />';

							echo '
							</dt>
						</dl>
					</div>';
	}

  echo '
<table border="0" cellpadding="0" cellspacing="0" width="100%">
  <tr class="windowbg2">
	<td width="28%" align="right"><b>Title</b>&nbsp;</td>
	<td width="72%"><input type="text" name="title" size="64" maxlength="100" value="' . $context['rpgwo_world']['title'] . '" /></td>
  </tr>
  
  <tr class="windowbg2">
	<td width="28%" align="right"><b>Description</b>&nbsp;</td>
	<td width="72%"><textarea name="description" rows="10" cols="100">' . $context['rpgwo_world']['description'] . '</textarea></td>
  </tr>
  
  
  
   <tr class="windowbg2">
	<td width="28%" align="right"><b>Server Host/IP Address</b>&nbsp;</td>
	<td width="72%"><input type="text" name="server_ip" size="64" value="' . $context['rpgwo_world']['server_ip'] . '" /></td>
  </tr>
  
   <tr class="windowbg2">
	<td width="28%" align="right"><b>Server Port</b>&nbsp;</td>
	<td width="72%"><input type="text" name="server_port" size="64" maxlength="100" value="' . $context['rpgwo_world']['server_port'] . '" /></td>
  </tr>
  
   <tr class="windowbg2">
	<td width="28%" align="right"><b>Server Update Url</b>&nbsp;</td>
	<td width="72%"><input type="text" name="server_updateurl" size="64" value="' . $context['rpgwo_world']['server_updateurl'] . '" /></td>
  </tr>
  
    
   <tr class="windowbg2">
	<td width="28%" align="right"><b>Server Version</b>&nbsp;</td>
		<td width="72%"><select name="server_version"><option value="1"' . ($context['rpgwo_world']['server_version'] == 1 ? ' selected="selected"' :'') .'>v1</option><option value="2"' . ($context['rpgwo_world']['server_version'] == 2 ? ' selected="selected"' :'') .'>v2</option><option value="3"' . ($context['rpgwo_world']['server_version'] == 3 ? ' selected="selected"' :'') .'>v3</option>  </select></td>

  </tr>
  
  <tr class="windowbg2">
	<td width="28%" align="right"><b>Enabled</b>&nbsp;</td>
	<td width="72%"><select name="enabled"><option value="1"' . ($context['rpgwo_world']['enabled'] == 1 ? ' selected="selected"' :'') .'>Yes</option><option value="0"' . ($context['rpgwo_world']['enabled'] == 0 ? ' selected="selected"' :'') .'>No</option> </select></td>
  </tr>

  
  <tr class="windowbg2">
	<td width="28%" colspan="2" align="center">
	<input type="submit" value="Add World" name="submit" />
	</td>
  </tr>
</table>
</form>  
  ';


}

function template_edit_world()
{
	global $context, $scripturl;



	echo '
<form method="post" action="' . $scripturl . '?action=rpgwo;sa=editworld2">
<div class="cat_bar">
		<h3 class="catbg centertext">
		Edit World
		</h3>
  </div>';

  if (!empty($context['rpgwo_errors']))
  {
	echo '<div class="errorbox" id="errors">
						<dl>
							<dt>
								<strong style="" id="error_serious">Errors</strong>
							</dt>
							<dt class="error" id="error_list">';

							foreach($context['rpgwo_errors'] as $msg)
								echo $msg . '<br />';

							echo '
							</dt>
						</dl>
					</div>';
	}

  echo '
<table border="0" cellpadding="0" cellspacing="0" width="100%">
  <tr class="windowbg2">
	<td width="28%" align="right"><b>Title</b>&nbsp;</td>
	<td width="72%"><input type="text" name="title" size="64" maxlength="100" value="' . $context['rpgwo_world']['title'] . '" /></td>
  </tr>
  
  <tr class="windowbg2">
	<td width="28%" align="right"><b>Description</b>&nbsp;</td>
	<td width="72%"><textarea name="description" rows="10" cols="100">' . $context['rpgwo_world']['description'] . '</textarea></td>
  </tr>

   <tr class="windowbg2">
	<td width="28%" align="right"><b>Server Host/IP Address</b>&nbsp;</td>
	<td width="72%"><input type="text" name="server_ip" size="64" value="' . $context['rpgwo_world']['server_ip'] . '" /></td>
  </tr>
  
   <tr class="windowbg2">
	<td width="28%" align="right"><b>Server Port</b>&nbsp;</td>
	<td width="72%"><input type="text" name="server_port" size="64" maxlength="100" value="' . $context['rpgwo_world']['server_port'] . '" /></td>
  </tr>
  
   <tr class="windowbg2">
	<td width="28%" align="right"><b>Server Update Url</b>&nbsp;</td>
	<td width="72%"><input type="text" name="server_updateurl" size="64" value="' . $context['rpgwo_world']['server_updateurl'] . '" /></td>
  </tr>
  
    
   <tr class="windowbg2">
	<td width="28%" align="right"><b>Server Version</b>&nbsp;</td>
		<td width="72%"><select name="server_version"><option value="1"' . ($context['rpgwo_world']['server_version'] == 1 ? ' selected="selected"' :'') .'>v1</option><option value="2"' . ($context['rpgwo_world']['server_version'] == 2 ? ' selected="selected"' :'') .'>v2</option><option value="3"' . ($context['rpgwo_world']['server_version'] == 3 ? ' selected="selected"' :'') .'>v3</option>  </select></td>

  </tr>
  
  <tr class="windowbg2">
	<td width="28%" align="right"><b>Enabled</b>&nbsp;</td>
	<td width="72%"><select name="enabled"><option value="1"' . ($context['rpgwo_world']['enabled'] == 1 ? ' selected="selected"' :'') .'>Yes</option><option value="0"' . ($context['rpgwo_world']['enabled'] == 0 ? ' selected="selected"' :'') .'>No</option> </select></td>
  </tr>

  
  <tr class="windowbg2">
	<td width="28%" colspan="2" align="center">
	<input type="hidden" name="id" size="64" value="' . $context['rpgwo_world']['ID_SERVER'] . '" />
	<input type="submit" value="Edit World" name="submit" />
	</td>
  </tr>
</table>
</form>  
  ';



}


function template_delete_world()
{
	global $context, $scripturl;

echo '
<form method="post" name="ftpform" action="',$scripturl,'?action=rpgwo;sa=deleteworld2">
	<div class="cat_bar">
		<h3 class="catbg centertext">
		Delete World?
		</h3>
  </div>
	<table border="0" width="100%" cellspacing="0" align="center" cellpadding="4" class="tborder">
	<tr class="windowbg2">
	<td colspan="2" align="center">
	You are deleting the world <b>' . $context['rpgwo_world']['title'] . '</b>
	
	</td>
</tr>
';



 echo '
<tr class="windowbg2">
	<td colspan="2" align="center">
	<input type="hidden" name="id" value="',$context['rpgwo_world']['ID_SERVER'],'" />
	<input type="submit" value="Delete World" />
	</td>
</tr>
</table>
</form>';

}