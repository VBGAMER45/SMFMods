<?php
/*---------------------------------------------------------------------------------
*	Ultimate Portal
*	Version 0.3
*	Project manager: vicram10
*	Copyright 2011
*	Powered by SMFSimple.com
**********************************************************************************/

if (!defined('SMF'))
	die('Hacking attempt...');
	


global $txt, $db_prefix, $scripturl;
global $smcFunc, $boarddir;
$limit = 10; // Cuantos se van a mostrar (?)
$mensaje = true; //Muestra el mensaje al pasar el mouse por encima del nombre
$sql = $smcFunc['db_query']('',"
      SELECT id_member, real_name, posts,date_registered
      FROM {db_prefix}members
      ORDER BY id_member DESC
      LIMIT 0,$limit ");
	  
   $context['nuevos'] = array();
   while ($row_members = $smcFunc['db_fetch_assoc']($sql))
   { 
      $context['nuevos'][] = array(
		 'posts' => $row_members['posts'],
		 'date_registered' => $row_members['date_registered'],
		 'id' => $row_members['id_member'],
         'name' => $row_members['real_name'],         
         'href' => $scripturl . '?action=profile;u=' . $row_members['id_member']
   );
   }
   $smcFunc['db_free_result']($sql);

echo '
	<table width="100%" border="0" style="font-size:10px;font-weight:bold;">
	<tr><td class="description" style="border-radius: 6px;text-align:center;">', $txt['nicknameup'] ,'</td><td class="description" style="text-align:center;border-radius: 6px;">', $txt['profile'] ,'</td></tr>
	';

foreach ($context['nuevos'] as $new)
{
	if($mensaje){
	echo '
	<tr><td align="center" style="padding-left:5px;">
	<a href="'.$new['href'].'" title="'.!empty($txt['lastUserWelcome']).' '.$new['name'].' '.!empty($txt['profilevisit']).'">'.$new['name'].'</a>
	</td><td align="center"><a href="'.$new['href'].'"><img src="', $settings['default_images_url'],'/ultimate-portal/icons/link.png" alt="" /></a></td></tr>';}
	else
	{
	echo '
	<tr><td>
	<img src="', $settings['default_images_url'],'/ultimate-portal/icons/circlegreenup.png" alt="" /> <a href="'.$new['href'].'" title="">'.$new['name'].'</a>
	</td><td align="right"><a href="'.$new['href'].'">', $txt['profile'] ,'</a></td></tr>';
	}
}
echo '
	</table>
';
?>